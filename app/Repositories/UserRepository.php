<?php

namespace App\Repositories;

use App\Exceptions\ActionIsWrong;
use App\Exceptions\ActionNotAllowed;
use App\Exceptions\GeneralException;
use App\Exceptions\ModelNotFound;
use App\Models\Permissions\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\ElasticHelper;
use App\Traits\UploadHelper;
use Carbon\Carbon;
use DB;
use Exception;

class UserRepository
{
    use ApiResponse, ElasticHelper, UploadHelper;

    protected $user;

    public function __construct(private $activityLogRepository = new ActivityLogRepository())
    {
        $this->user = new User();
    }

    /**
     * List All Users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function listUsers($request, $withPaginator = true)
    {
        $query = $this->user->where('id', '!=', 1)->where('type', '!=', 'SELLER');
        $withTrashed = request('trashed', 'hide');

        $query->when($withTrashed == 'show', function ($query) {
            $query->onlyTrashed();
        });

        //apply user type
        if (! is_null($request->type)) {
            $query->ofType(mb_strtoupper($request->type));
        }

        //filters
        $query = $this->customSearch($query, $request->query('custom_search', []));

        if ($request->search) {
            if (! is_null($request->search['value'])) {
                $query = $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search['value']}%")
                        ->orWhere('email', 'like', "%{$request->search['value']}%")
                        ->orWhere('id', $request->search['value'])
                        ->orWhereHas('roles', function ($qu) use ($request) {
                            $qu->where('name', 'like', "%{$request->search['value']}%");
                        });
                });
            }
        }

        if (isset($request['ids'])) {
            $ids = explode(',', $request['ids']);
            $query = $query->whereIn('id', $ids);
        }
        $data = $query->orderBy('created_at', 'desc');
        if ($withPaginator) {
            return $data->paginate($request->offset ?? 10);
        }

        return $data->get();
    }

    /**
     * Create New Role
     *
     * @param  \App\Http\Requests\User\CreateUserRequest  $request
     * @return array
     */
    public function createUser($request)
    {
        try {
            $this->user->type = strtoupper($request->type);
            $this->user->name = $request->name;
            $this->user->email = $request->email;
            $this->user->password = bcrypt($request->password);
            $this->user->verification_code = 'VERIFIED';
            $this->user->save();

            return [
                'model' => $this->user->id,
                'type' => strtolower($request->type),
            ];
        } catch (Exception $e) {
            return $this->error(__('app.save_error.title'));
        }
    }

    /**
     * Get a user by ID.
     *
     * @param  int  $id
     */
    public function getUserById($id): mixed
    {
        return User::find($id);
    }

    /**
     * Update Personal Information
     *
     * @param  \App\Http\Requests\User\UpdateUserRequest  $request
     * @return void
     */
    public function updateUser($request, string $id)
    {
        try {
            $user = $this->user->find($id);
            if (! $user) {
                throw new ModelNotFound('User Not Found');
            }
            //upload image
            if ($request->hasFile('image')) {
                $mainPath = $this->storeFile($request, 'image', ['type' => 'user_images']);
                $oldImage = $user->image;
                $request->merge([
                    'image' => $mainPath,
                ]);
            }

            //change password if not null
            if (! is_null($request->password)) {
                $request->merge([
                    'password' => bcrypt($request->password),
                ]);
                $user->update(
                    [
                        'email' => $request->email ?? $user->email,
                        'name' => $request->name ?? $user->name,
                        'image' => $request->image ?? $user->image,
                        'phone_number' => $request->phone_number ?? $user->phone_number,
                        'city_id' => $request->city_id ?? $user->city_id,
                        'password' => $request->password ?? $user->password,
                    ]);
            } else {
                $user->update($request->except('password', 'password_confirmation'));
            }

            if (isset($oldImage)) {
                $this->deleteFiles([$oldImage], ['type' => 'user_images']);
            }
        } catch (ModelNotFound $modelNotFoundEx) {
            throw $modelNotFoundEx;
        } catch (Exception $e) {
            if (isset($mainPath)) {
                $this->deleteFiles([$mainPath], ['type' => 'user_images']);
            }
            throw new GeneralException('Error Saving To DB in: '.$e->getMessage());
        }
    }

    /**
     * Delete User
     *
     * @return void
     */
    public function deleteUser(string $id)
    {
        try {
            //check the user is not deleting himself
            if (auth()->user()->id == $id) {
                throw new ActionNotAllowed(__('app.permission_error.description'));
            }
            //get user entity by id
            $user = $this->user->where('id', '!=', 1)->with(['products' => function ($query) {
                $query->withDisabled();
            }])->withTrashed()->find($id);

            if (! $user) {
                throw new ModelNotFound('User Not Found');
            }

            if (! $user->trashed()) {
                //check if user not has products
                if ($user->sellerOrders->count() > 0) {
                    throw new ActionIsWrong(__('app.delete_seller_has_products_error.description'));
                }
                try {
                    DB::table('product')->where('seller_id', $user->id)->update(['deleted_at' => Carbon::now()]);
                    $this->deleteProductsBySeller($user->id);
                    $user->delete();

                    return;
                } catch (Exception $e) {
                    return $this->error(__('app.delete_error.description'));
                }
            }
            DB::transaction(function () use ($user) {
                $urls = [];
                if ($user->image) {
                    $urls[] = $user->image;
                }
                if (! is_null($user->userCustomField)) {
                    foreach ($user->userCustomField::UPLOAD_ATTRIBUTES as $field) {
                        if ($user->userCustomField->$field) {
                            $urls[] = $user->userCustomField->$field;
                        }
                    }
                    if ($user->userCustomField->cover) {
                        $urls[] = $user->userCustomField->cover;
                    }
                    $this->deleteFiles($urls, ['type' => 'user_images']);
                    $user->userCustomField()->delete();
                }
                $user->forceDelete();
            });
        } catch (ActionNotAllowed $actionNotAllowedEx) {
            throw $actionNotAllowedEx;
        } catch (ModelNotFound $modelNotFoundEx) {
            throw $modelNotFoundEx;
        } catch (ActionIsWrong $actionIsWrongEx) {
            throw $actionIsWrongEx;
        } catch (Exception $actionIsWrongEx) {
            throw new GeneralException(__('app.delete_error.description'));
        }
    }

    /**
     * Restore User
     *
     *
     * @return void
     */
    public function restoreUser(string $id)
    {
        try {
            $user = $this->user->with(['products' => function ($q) {
                $q->withTrashed();
                $q->withDisabled();
            }])->withTrashed()->find($id);

            if (! $user) {
                throw new ModelNotFound('User Not Found');
            }

            if (! $user->trashed()) {
                throw new ActionIsWrong(__('app.restore_error.already_restored'));
            }

            DB::transaction(function () use ($user) {
                DB::table('product')->where('seller_id', $user->id)->update(['deleted_at' => null]);
                $this->createProductsBySeller($user->id);
                $user->restore();
            });
        } catch (ModelNotFound $modelNotFoundEx) {
            throw $modelNotFoundEx;
        } catch (ActionIsWrong $actionIsWrongEx) {
            throw $actionIsWrongEx;
        } catch (Exception $e) {
            throw new GeneralException(__('app.restore_error.description'));
        }
    }

    /**
     * Change default lang to user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return void
     */
    public function changeDefaultLang($request, $id)
    {
        $user = $this->user->find($id);
        if (! ($user)) {
            throw new ModelNotFound(__('app.change_lang.user_not_found'));
        }

        if (! in_array($request->lang, ['en', 'tr'])) {
            throw new ModelNotFound(__('app.change_lang.lang_not_found'));
        }
        $user->lang = $request->lang;
        $user->save();
    }

    /**
     * Change status and role
     *
     * @param \App\Http\Requests\User\ChangeStatusAndRoleRequest
     * @param  string  $id
     * @return void
     */
    public function changeStatusAndRole($request, $id)
    {
        $user = $this->user->where('id', '!=', 1)->where('type', '!=', 'SELLER')->find($id);
        if (! ($user)) {
            throw new ModelNotFound(__('app.change_lang.user_not_found'));
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $user->status = mb_strtoupper($request->status);
                $user->save();
                // Assign Role
                if (isset($request->role_code)) {
                    $oldRoles = Role::whereIn('id', $user->roles->pluck('id'))->get()->pluck('title');
                    $user->syncRoles($request->role_code);
                    $newRoles = Role::whereIn('id', $user->roles->pluck('id'))->get()->pluck('title');
                    $admin = auth()->user();
                    if ($oldRoles != $newRoles) {
                        $this->activityLogRepository->create([
                            'log_name' => 'default',
                            'description' => 'updated',
                            'subject_id' => $user->id,
                            'subject_type' => User::class,
                            'causer_id' => $admin->id,
                            'causer_type' => User::class,
                            'properties' => [
                                'attributes' => [
                                    'roles' => $newRoles,
                                ],
                                'old' => [
                                    'roles' => $oldRoles,
                                ],
                            ],
                            'event' => 'updated',
                        ]);
                    }
                }
            });
        } catch (ModelNotFound $me) {
            throw $me;
        } catch (Exception $e) {
            throw new GeneralException(__('app.save_error.description'));
        }
    }

    /**
     * List users data for search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function searchData($request)
    {
        $term = trim($request->search);
        if (! $request->page) {
            $request->page = 1;
        }
        $list = $this->user
            ->when(isset($request->type), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->where(function ($q) use ($term) {
                return $q->orWhere('username', 'LIKE', "%{$term}%")
                    ->orWhere('id', 'LIKE', $term)
                    ->orWhere('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%");
            });
        $list = $list->paginate($request->items_per_page);
        $result['results'] = [];
        foreach ($list as $key => $item) {
            $result['results'][$key] = $item->formAjaxArray(true);
        }
        $lastPage = $list->lastPage();
        $result['pagination']['more'] = $request->page >= $lastPage ? false : true;

        return $result;
    }

    /**
     * List users type data for search.
     *
     * @return mixed
     */
    public function searchTypeData()
    {
        $excludedTypes = ['ROOT'];
        $list = $this->user->get()->pluck('type')->unique()->reject(function ($type) use ($excludedTypes) {
            return in_array($type, $excludedTypes);
        })->values();
        $result['results'] = [];
        foreach ($list as $key => $item) {
            $result['results'][$key] = [
                'id' => $item,
                'text' => $item,
                'selected' => true,
            ];
        }
        $result['pagination']['more'] = false;

        return $result;
    }

    /**
     * Search in users with key and value
     *
     * @param    $users
     * @param  array  $filters
     * @return mixed
     */
    public function customSearch($query, $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value == '') {
                continue;
            }
            if ($key == 'type') {
                $query->ofType(mb_strtoupper($value));
            } elseif ($key == 'date_from') {
                $query->where('created_at', '>=', Carbon::parse($value));
            } elseif ($key == 'date_to') {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay());
            } elseif ($key == 'last_ordered_date') {
                $query->whereDoesntHave('userOrders', function ($query) use ($value) {
                    $query->where('created_at', '>=', Carbon::parse($value));
                });
            } else {
                $query->where($key, $value);
            }
        }

        return $query;
    }
}
