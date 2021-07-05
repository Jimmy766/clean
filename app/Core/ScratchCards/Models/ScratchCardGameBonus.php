<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\ScratchCards\Models\ScratchCard;
    use DateInterval;
    use DateTime;
    use Exception;
    use Illuminate\Database\Eloquent\Model;

    class ScratchCardGameBonus extends Model
    {
        const EXPIRATION_DATE_INTERVAL = 'P1Y'; // represent 1 Year
        public $timestamps = false;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $table = 'scratches_game_bonus';

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function scratch_card() {
            return $this->hasOne(ScratchCard::class, 'id', 'scratches_id');
        }

        /**
         * @return DateTime
         */
        public function getDefaultExpirationDateAttribute() {
            $date = new DateTime();
            try {
                $date->add(new DateInterval(self::EXPIRATION_DATE_INTERVAL));
            } catch (Exception $e) {
            }
            return $date;
        }
    }
