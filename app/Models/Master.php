<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Master extends Model
{
    use HasFactory, SoftDeletes, ModelBase;

    private static string $password = '1234qwer';

    protected $fillable = [
        'origin_id', 'specialization', 'avatar', 'schedule_till', 'user_id', 'team_id', 'deleted_at', 'name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function currency()
    {
        return $this->team->currency() ?? null;
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }

    public function budgets()
    {
        return $this->belongsToMany(Budget::class)->withTimestamps();
    }

    public function getRecords(string $startDate, string $endDate, bool $attendance = true, bool $onlyConversionServices = false)
    {
        $records = $this->records()
            ->whereBetween(DB::raw('DATE(started_at)'), array($startDate, $endDate))
            ->where('attendance', $attendance)
            ->get();

        if ($onlyConversionServices) {
            $records = $records->filter(function ($record) {
                return $record->services->filter(function ($service) {
                    return $service->conversion;
                });
            });
        }

        return $records;
    }

    public function solveComission(string $startDate, string $endDate)
    {
        return Record::solveComission($this->getRecords($startDate, $endDate));
    }

    public function getBudget(string $date, int $budgetTypeId)
    {
        return $this->budgets
            ->where('date', $date)
            ->where('budget_type_id', $budgetTypeId)
            ->first();
    }

    private static function peel(array $item)
    {
        if (!empty($item['id'])) {
            $item['origin_id'] = intval(trim($item['id']));
            unset($item['id']);
        }

        if (($item['fired'] ?? 1) == 1 || ($item['hidden'] ?? 1 == 1)) {
            $item['deleted_at'] = date(config('app.iso_datetime'));
        }

        return $item;
    }

    public static function seed($items)
    {
        $collection = collect($items)->filter(function ($item) {
            return $item["specialization"] != "Косметолог";
        });

        $collection->each(function ($item) {
            self::createOrUpdate(self::peel($item));
        });

        note("info", "master:seed", "Обновлены мастера из апи", self::class);
    }

    private static function createOrUpdate(array $item)
    {
        if (empty($item['origin_id'])) {
            return null;
        }

        return self::createOrUpdateWithRelations($item, self::findByOriginId($item['origin_id']));
    }

    private static function createOrUpdateWithRelations(array $data, ?Master $master)
    {
        if (empty($master)) {
            $master = self::create($data);
        } else {
            $master->update($data);
            $master->refresh();
        }

        if (empty($data['deleted_at'] ?? null)) {
            $master->restore();
        }

        $role = Role::findByCode('master');

        if (empty($master->user)) {
            $user = User::create([
                'account' => translit($data['name'] . "-" . $data['origin_id']),
                'email' => $data['user']['email'] ?? null,
                'phone' => $data['user']['phone'] ?? null,
                'password' => bcrypt(self::$password),
                'open_password' => self::$password,
                'role_id' => $role->id
            ]);

            $master->user()->associate($user);
            $master->save();
        } else {
            $user = $master->user->update([
                'email' => $data['user']['email'] ?? null,
                'phone' => $data['user']['phone'] ?? null
            ]);
        }

        return $master;
    }
}
