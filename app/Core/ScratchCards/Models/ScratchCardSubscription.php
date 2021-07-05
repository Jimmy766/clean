<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\Carts\Models\CartScratchCardSubscription;
    use App\Core\ScratchCards\Models\ScratchCard;
    use App\Core\ScratchCards\Models\ScratchCardGameBonus;
    use App\Core\ScratchCards\Models\ScratchCardGameToken;
    use App\Core\Casino\Services\PariplayIntegration;
    use App\Core\Base\Traits\LogCache;
    use App\Core\Rapi\Models\Movement;
    use App\Core\ScratchCards\Transforms\ScratchCardSubscriptionTransformer;
    use App\Core\Users\Models\User;
    use DateTime;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Auth;


    class ScratchCardSubscription extends Model
    {
        use LogCache;

        const MOVEMENT_GENERATE_WIN = 60;
        const MOVEMENT_CANCEL_WIN = 61;
        public $timestamps = false;
        public $transformer = ScratchCardSubscriptionTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'scratches_sub_id';
        protected $table = 'scratches_subscriptions';

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function cart_subscription() {
            return $this->hasOne(CartScratchCardSubscription::class, 'cts_id', 'scratches_cts_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function scratch_card() {
            return $this->hasOne(ScratchCard::class, 'id', 'scratches_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function user() {
            return $this->belongsTo(User::class, 'usr_id', 'usr_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function movements() {
            return $this->hasMany(Movement::class, 'sub_id', 'scratches_sub_id');
        }

        public function getPrizeAttribute() {
            $how_much = 0;
            $this->movements->whereIn('typ_id', $this->scrachesMovements())->each(function ($item) use (&$how_much) {
                if ($item->typ_id == ScratchCardSubscription::MOVEMENT_GENERATE_WIN) {
                    $how_much += (float)$item->mov_value;
                } elseif ($item->typ_id == ScratchCardSubscription::MOVEMENT_CANCEL_WIN) {
                    $how_much -= (float)$item->mov_value;
                }
            });
            return $how_much;
        }

        /**
         * @return array
         */
        private function scrachesMovements() {
            return [self::MOVEMENT_GENERATE_WIN, self::MOVEMENT_CANCEL_WIN];
        }

        public function getDrawDateAttribute(){
            return $this->movements->whereIn('typ_id', $this->scrachesMovements())->first()->mov_date;
        }

        public function isActive() {
            return ((($this->sub_rounds + $this->sub_rounds_extra) > $this->sub_emitted) ||
                ($this->sub_rounds_free > $this->sub_emitted_free)) &&
                $this->sub_status != 2;
        }

        public function isInactive() {
             return ($this->sub_rounds > 0 && ($this->sub_rounds + $this->sub_rounds_extra == $this->sub_emitted)) ||
                ($this->sub_rounds_free > 0 && ($this->sub_rounds_free == $this->sub_emitted_free)) ||
                $this->sub_status == 2;
        }
        /**
         * @return string
         */
        public function getStatusAttribute() {
            return $this->isActive() ? trans('lang.active_subscription') : ($this->isInactive() ? trans('lang.expired_subscription') : '');
        }

        /**
         * @return string
         */
        public function getStatusTagAttribute() {
            return $this->isActive() ? trans('lang.active_subscription_tag') : ($this->isInactive() ? trans('lang.expired_subscription_tag') : '');
        }

        /**
         * @return integer
         */
        public function getRemainingRoundsAttribute() {
            return ($this->sub_rounds + $this->sub_rounds_free) - $this->sub_emitted - $this->sub_emitted_free;
        }

        /**
         * @return integer
         */
        public function getRemainingRoundsExtraAttribute() {
            return $this->sub_rounds_extra - $this->sub_emitted_extra;
        }

        /**
         * @return string
         */
        public function getRealPlayUrlAttribute() {
            return $this->scratch_card->realPlayUrl($this->scratches_sub_id);
        }

        /**
         * @return bool
         */
        public function canPlay() {
            return ($this->remaining_rounds + $this->remaining_rounds_extra) > 0;
        }

        public function srcReal($is_mobile, $language) {
            $user = Auth::user();
            $this->record_log('pariplay', "Call real play with Subscription: {$this->scratches_sub_id}, User: {$this->usr_id}");
            $this->checkFreeRounds($is_mobile, $user);
            $response = PariplayIntegration::getResponsePlayUrl($this->scratch_card, $is_mobile, $language, 'real', $user);
            if (isset($response->Token))
                $this->addToken($response, $user);
            return PariplayIntegration::prepareResponse($response);
        }

        private function checkFreeRounds($is_mobile, $user): void {
            if (!$this->game_bonus && $this->sub_rounds_free > 0) {
                $bonus = new ScratchCardGameBonus();
                $bonus->scratches_id = $this->scratches_id;
                $bonus->free_rounds = $this->sub_rounds_free;
                $bonus->expiration_date = $bonus->default_expiration_date;
                $response_bonus = PariplayIntegration::getBonusId($bonus, $is_mobile, $user);
                if (isset($response_bonus->BonusId)) {
                    $bonus->bonus_id = $response_bonus->BonusId;
                    $bonus->usr_id = $this->usr_id;
                    $bonus->country_code = $user->country->country_Iso;
                    $bonus->curr_code = $user->country->country_info->country_currency;
                    $bonus->rounds_played = 0;
                    $bonus->bet_level = 0;
                    $bonus->coin_value = 0;
                    $bonus->line_number = 0;
                    $bonus->use_points = 0;

                    $this->game_bonus()->save($bonus);

                    $this->bonus_id = $response_bonus->BonusId;
                    $this->save();
                }
            }
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function game_bonus() {
            return $this->hasOne(ScratchCardGameBonus::class, 'sub_id', 'scratches_sub_id');
        }

        private function addToken($response, $user) {
            $token = new ScratchCardGameToken();
            $token->token = $response->Token;
            $token->url = $response->Url;
            $token->scratches_id = $this->scratches_id;
            $token->usr_id = $this->usr_id;
            $token->curr_code = $this->cart_subscription->cart->crt_currency;
            $token->lastRoundId = '';
            $token->dateInserted = new DateTime();
            $token->gameHasEnded = 0;
            $token->financialMode = $response->FinancialMode;
            $this->game_token()->save($token);
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function game_token() {
            return $this->hasMany(ScratchCardGameToken::class, 'scratches_sub_id', 'scratches_sub_id');
        }

        public function getScratchNameAttribute() {
            return $this->scratch_card ? $this->scratch_card->name : '';
        }
    }
