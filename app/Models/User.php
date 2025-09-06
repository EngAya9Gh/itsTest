<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\HasApiTokens;
use App\Mail\PasswordReset;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table="users";
    protected $fillable = [
        'name',
      'google2fa_secret',
        'first_name',
        'last_name',
        'mobile',
        'role',//1,2,3,4  --- 1 is admin
        'agent_id',
        'vip_id',
        'image',
        'image_url',
        'email',
        'gender',
        'nationality',
        'email_verified_at',
        'password',
        'currency',
      'balance',
        'balance_profit',
      'preferred_currency_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
      public function preferredCurrency()
    {
        return $this->belongsTo(Currency::class, 'preferred_currency_id');
    }
    public function services(): BelongsToMany
    {
        return $this->BelongsToMany(Service::class, 'service_orders', 'user_id', 'service_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function vip(): BelongsTo
    {
        return $this->belongsTo(Vip::class);
    }

  /*  public function turkificationOrders(): HasMany
    {
        return $this->hasMany(TurkificationOrder::class);
    }

    public function transferOrders(): HasMany
    {
        return $this->hasMany(TransferOrder::class);
    }



    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'card_orders', 'card_id', 'user_id');
    }

  */
    public function tweetcells(): BelongsToMany
    {
        return $this->belongsToMany(Tweetcell::class, 'tweetcell_orders', 'tweetcell_id', 'user_id');
    }
     public function tweetcellKontors(): BelongsToMany
    {
        return $this->belongsToMany(TweetcellKontor::class, 'tweetcell_kontor_orders', 'tweetcell_kontor_id', 'user_id');
    }

/*
   public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'app_orders', 'app_id', 'user_id');
    }
    public function ebanks(): BelongsToMany
    {
        return $this->BelongsToMany(Ebank::class, 'ebank_orders', 'user_id', 'ebank_id');
    }



    public function ecards(): BelongsToMany
    {
        return $this->BelongsToMany(Ecard::class, 'ecard_orders', 'ecard_id', 'user_id');
    }

    public function games(): BelongsToMany
    {
        return $this->BelongsToMany(Game::class, 'game_orders', 'user_id', 'game_id');
    }
    public function DataCommunications(): BelongsToMany
    {
        return $this->belongsToMany(DataCommunication::class, 'data_communication_orders', 'data_communication_id', 'user_id');
    }
*/
// إرفاق حدث الحذف
    protected static function booted()
    {
        static::deleting(function ($user) {
            // حذف السجلات المرتبطة مثل الخدمة المفضلة
            $user->favorites()->delete();
            $user->tweetcells()->detach();
            $user->services()->detach();
            $user->tweetcellKontors()->detach();
            $user->transferMoneyFirms()->detach();
        });
    }
   /* public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_orders', 'program_id', 'user_id');
    }*/

    public function transferMoneyFirms(): BelongsToMany
    {
        return $this->belongsToMany(TransferMoneyFirm::class, 'transfer_money_firm_orders', 'transfer_money_firm_id', 'user_id');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $resetLink = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        Mail::to($this->email)->send(new PasswordReset($resetLink, $this->name ?? $this->first_name));
    }

}
