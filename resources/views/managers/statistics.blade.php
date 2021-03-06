@extends('adminlte::page')

@section('content_header')
<x-week-header header="Статистика"></x-week-header>
@stop

@section('content')
@foreach($teams as $team)
<h4>
    {{ $team->title }}
</h4>
<div class="row">
    <div class="col-md-8">
        @forelse($team->masters as $master)
        @include('masters.week-services', ['master' => $master])
        @empty
        <div class="card">
            <div class="card-body">
                @lang("common.no-data")
            </div>
        </div>
        @endforelse
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <b>Оператор</b>
                        <span class="float-right">
                            @if (!empty($team->operator))
                            <span class="badge badge-warning" title="Суммарный бонус">
                                {{ $team->operator->getPoints($team->operator->getProfit(week()->start(), week()->end())) }}
                            </span>
                            {{ $team->operator->name }}
                            @else
                            @lang('common.no-data')
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Конверсия записей</b>
                        <span class="float-right">
                            {{ $team->solveConversion(week()->start(), week()->end(), "records") }}%
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Конверсия пришедших</b>
                        <span class="float-right">
                            {{ $team->solveConversion(week()->start(), week()->end(), "attendance_records") }}%
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<hr>
@endforeach
@stop
