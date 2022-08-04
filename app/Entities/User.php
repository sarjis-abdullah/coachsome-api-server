<?php

namespace App\Entities;

use App\Data\Constants;
use App\Entities\Role as RoleEntity;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\PasswordReset;
use App\Notifications\PWA\PasswordReset as NotificationsPWAPasswordReset;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Rickycezar\Impersonate\Models\Impersonate;

/**
 * Class User.
 */
class User extends Authenticatable
{
    use SoftDeletes, HasApiTokens, Notifiable, LaratrustUserTrait, Impersonate;


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'uuid',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function create(array $attributes = [])
    {
        if (array_key_exists('password', $attributes)) {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $model = static::query()->create($attributes);

        return $model;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token, $this));
    }


    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotificationPWA($otp)
    {
        $this->notify(new NotificationsPWAPasswordReset($otp, $this));
    }

    /**
     * This method comes from HasRolesUuid traits basically it override HasRoles::getStoredRole method
     * It was created an error when I put it as follow. So I take the function as below.
     *
     * HasRolesUuid {
     *   HasRolesUuid::getStoredRole insteadof HasRoles;
     * }
     *
     * @param $role
     *
     * @return Role
     */
    protected function getStoredRole($role)
    {
        if (is_string($role)) {
            return app(RoleEntity::class)->where('name', $role)->orWhere('uuid', $role)->first();
        }

        return $role;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id')->where('user_role', $this->roles[0]->name);
    }

    public function distance()
    {
        return $this->hasOne(Distance::class, 'user_id');
    }

    public function socialAccount()
    {
        return $this->hasOne(SocialAccount::class, 'user_id');
    }

    public function ownPackageSetting()
    {
        return $this->hasOne(PackageUserSetting::class, 'user_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'user_id');
    }

    public function defaultAvailability()
    {
        return $this->hasOne(UserDefWeekAvailability::class, 'user_id');
    }

    public function payoutInformation()
    {
        return $this->hasOne(PayoutInformation::class, 'user_id');
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class, 'user_id');
    }

    public function activityStatus()
    {
        return $this->belongsTo(ActivityStatus::class, 'activity_status_id');
    }

    public function starStatus()
    {
        return $this->belongsTo(StarStatus::class, 'star_status_id');
    }

    public function availabilities()
    {
        return $this->hasMany(UserWeekAvailability::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'user_id');
    }


    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'user_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'user_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'user_id');
    }

    public function onBoardingSportCategories()
    {
        return $this->belongsToMany(SportCategory::class, 'sport_category_user', 'user_id', 'sport_category_id')->where('is_onboarding', 1);
    }

    public function onBoardingSportTags()
    {
        return $this->hasMany(SportTag::class, 'user_id')->where('is_onboarding', 1);
    }

    public function onBoardingLanguages()
    {
        return $this->belongsToMany(Language::class, 'language_user', 'user_id', 'language_id')->where('is_onboarding', 1);
    }

    public function sportCategories()
    {
        return $this->belongsToMany(SportCategory::class, 'sport_category_user', 'user_id', 'sport_category_id')->where('user_role', $this->roles[0]->name);
    }

    public function sportTags()
    {
        return $this->hasMany(SportTag::class, 'user_id')->where('user_role', $this->roles[0]->name);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'language_user', 'user_id', 'language_id')->where('user_role', $this->roles[0]->name);
    }

    public function switchInfo(){
        return $this->hasOne(ProfileSwitch::class, 'user_id');
    }

    public function generalProfile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function generalSportCategories()
    {
        return $this->belongsToMany(SportCategory::class, 'sport_category_user', 'user_id', 'sport_category_id');
    }

    public function generalSportTags()
    {
        return $this->hasMany(SportTag::class, 'user_id');
    }

    public function generalLanguages()
    {
        return $this->belongsToMany(Language::class, 'language_user', 'user_id', 'language_id');
    }

    /**
     * The featured tags that belong to the user.
     */
    public function featuredTags()
    {
        return $this->belongsToMany(FeaturedTag::class,'featured_tag', 'user_id','feature_tag_id' );
    }

    public function info()
    {
        $info = new \stdClass();
        $info->first_name = $this->first_name;
        $info->last_name = $this->last_name;
        $info->email = $this->email;
        $info->image = $this->profile->image ?? null;
        $info->user_name = $this->user_name ?? null;
        $info->role = $this->load('roles')->roles->first() ?? '';
        return $info;
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function profileName()
    {
        $name = '';
        if($this->profile){
            $name = $this->profile->profile_name;
        } else {
            $name = $this->first_name . ' ' . $this->last_name;
        }
        return $name;
    }

    public function emailAddress()
    {
        return $this->email;
    }

    public function isActive()
    {
        return $this->activity_status_id == Constants::ACTIVITY_STATUS_ID_ACTIVE;
    }

    public function getHasPasswordAttribute()
    {
        return ! empty($this->attributes['password']);
    }


}
