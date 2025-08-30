    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>قسم الطلبات </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active"></li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                   
                        <div class="p-2 d-flex">
                        </div>
                    </div>
                </div>
            </div>    
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="header">
                            <h2>الطلبات</h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr> 
                                            <th>اسم الخدمة</th>
                                            <th>اسم المستخدم</th>
                                            <th>رقم الهاتف</th>
                                            <th>السعر</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $key => $gameOrder)
                                        <tr>
                                            <td class="project-title">
                                                <h6>{{$gameOrder->service_name}}</h6>
                                            </td>
                                            <td>{{$gameOrder->user_name}}</td>
                                            <td>{{$gameOrder->mobile}}</td>
                                           
                                            <td>{{$gameOrder->price}}</td>
                                           @if($gameOrder->status==1)
                                                <td>قيد الانتظار</td>
                                            @elseif($gameOrder->status==2)
                                                <td>تمت بنجاح </td>
                                             @else 
                                                <td> الغاء</td>
                                            @endif
                                           
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






@endsection