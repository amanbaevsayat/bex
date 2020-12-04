<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes, ModelBase;

    protected $fillable = [
        "title", "premium_rate", "operator_id", "city_id"
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    public function masters()
    {
        return $this->hasMany(Master::class);
    }

    public function cosmetologists()
    {
        return $this->hasMany(Cosmetologist::class);
    }

    public function currency()
    {
        return $this->city->country->currency ?? null;
    }

    public function currencyRate(string $date)
    {
        return CurrencyRate::findByCurrencyAndDate($this->team->currency(), $date);
    }

    public function budgets()
    {
        return $this->belongsToMany(Budget::class)->withTimestamps();
    }

    public static function seedOutcomes(string $date)
    {
        $budgetTypeInstagram = BudgetType::findByCode("marketer:team:instagram:outcome");
        $budgetTypeVK = BudgetType::findByCode("marketer:team:vk:outcome");

        $teams = self::all();

        $budget = Budget::findByDateAndType($date, $budgetTypeInstagram);
        if (empty($budget)) {
            Budget::create([
                "date" => $date,
                "json" => $teams->map(function ($team) {
                    return [
                        "team_id" => $team->id,
                        "amount" => 0
                    ];
                })->toJson(),
                "budget_type_id" => $budgetTypeInstagram->id,
            ]);
        }

        $budget = Budget::findByDateAndType($date, $budgetTypeVK);
        if (empty($budget)) {
            Budget::create([
                "date" => $date,
                "json" => $teams->map(function ($team) {
                    return [
                        "team_id" => $team->id,
                        "amount" => 0
                    ];
                })->toJson(),
                "budget_type_id" => $budgetTypeVK->id,
            ]);
        }

        note("info", "budget:seed", "Созданы затраты на команду на дату {$date}", Budget::class);
    }

    public function solveComission(string $startDate, string $endDate)
    {
        // get total team comission in KZT
        $comission = $this->masters->sum(function ($master) use ($startDate, $endDate) {
            return $master->solveComission(
                $startDate,
                $endDate
            );
        });

        $comission += $this->cosmetologists->sum(function ($cosmetologist) use ($startDate, $endDate) {
            return $cosmetologist->getComission($startDate, $endDate) ?? 0;
        });

        return $comission * floatval($this->premium_rate);
    }

    public function solveConversion(string $startDate, string $endDate, bool $onlyAttendance)
    {
        // get attendance records
        $recordsCount = $this->masters->sum(function ($master) use ($startDate, $endDate, $onlyAttendance) {
            $count = $master->getRecords($startDate, $endDate, true, true)->count();
            if (!$onlyAttendance) {
                $count += $master->getRecords($startDate, $endDate, false, true)->count();
            }
            return $count;
        });

        // get new contacts
        $team = $this;
        $contactDifference = ContactType::all()
            ->sum(function ($contactType) use ($startDate, $endDate, $team) {
                return Contact::getDifference($startDate, $endDate, $team, $contactType);
            });

        return $contactDifference == 0 ? 0 : round($recordsCount / $contactDifference * 100, 2);
    }

    public function contacts(string $startDate, string $endDate)
    {
        $contactTypes = ContactType::all();
        $contacts = collect();
        foreach ($contactTypes as $contactType) {
            $contacts = $contacts->merge(Contact::getByDatesTypeTeam($startDate, $endDate, $this, $contactType));
        }

        return $contacts->groupBy("date");
    }
}
