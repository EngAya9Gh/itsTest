    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>المستخدمين</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">المستخدمين </li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action"> 
 <!--      <a href="javascript:void(0);" data-toggle="modal"  class="btn btn-primary" data-target="#createmodal" ><i class="fa fa-add">أضف مستخدمًا</i></a>
-->     </div>
                        <div class="p-2 d-flex">
                        </div>
                    </div>
                </div>
            </div>    
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="header">
                            <h2> محفظة العملاء</h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                            <th>اسم المستخدم</th>
                                            <th>الرصيد الحالي</th>
                                            <th> الوارد</th>
                                            <th>الصادر</th>
                                            <th>ربح لم يتم سحبه</th>
                                            <th>اجمالي الربح</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $key => $user)
                                        <tr>
                                            <td class="project-title">
                                                <h6>{{$user->name}}</h6>
                                                <small>{{$user->first_name.' '}}{{$user->last_name}}</small>
                                                 <small>( 
                                                   
                                                          @if($user->role==4)
                                                         زبون عادي
                                                         @elseif   ($user->role==2) 
                                                             وكيل
                                                          @elseif   ($user->role==3)
                                                            صاحب محل
                                                          @else
                                                            مسؤول
                                                          @endif
                                                   )  </small>
                                            </td>
                                           
                                            <td>
                                            <span class="badge badge-success">
                                               {{$user->balance}}TL
                                               <span>
                                            </td>
                                            <td>{{$user->financials['incoming']}}</td>
                                            <td>{{$user->financials['outgoing']}}</td>
                                            <td>{{$user->financials['profitTotals']}}</td>
                                            <td> {{$user->balance_profit}}TL </td>
                                            
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

