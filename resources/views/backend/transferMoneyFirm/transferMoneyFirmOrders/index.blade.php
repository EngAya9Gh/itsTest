    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>  خدمة تعبئة الرصيد </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">تعبئة الرصيد</li>
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
                            <h2> طلبات شركات الشحن </h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                            <th>اسم الزبون</th>
                                            <th>اسم الشركة</th>
                                            <th>المبلغ </th>
                                            <th>القيمة الفعلية</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transferMoneyFirmOrders as $key => $transferMoneyFirmOrder)
                                      @if($transferMoneyFirmOrder->status=='قيد المراجعة')
                                        <tr>
                                            <td class="project-title">
                                                <h6>{{$transferMoneyFirmOrder->user_name}}</h6>
                                            </td>
                                            <td class="project-title">
                                                <h6>{{$transferMoneyFirmOrder->transfer_money_firm_name}}</h6>
                                            </td>
                                     
                                            <td>{{$transferMoneyFirmOrder->value}}{{$transferMoneyFirmOrder->currency}}</td> 
                                             <td>{{$transferMoneyFirmOrder->amount}}{{$baseCurrency->name}}</td>
                                       
                                            <td>{{$transferMoneyFirmOrder->status}}</td>
                                            <td class="project-actions">
                                                <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal">
                                                <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary"   data-toggle="modal" data-target="#balanceModal{{ $transferMoneyFirmOrder->user_id}}"><i class="icon-plus"></i></a>

                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#editModal{{$transferMoneyFirmOrder->id}}" class="btn btn-sm btn-outline-success"><i class="icon-pencil"></i></a>
                                                <a  href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$transferMoneyFirmOrder->id}}" class="btn btn-sm btn-outline-danger" ><i class="icon-trash"></i></a>
                                                @if($transferMoneyFirmOrder->status=='قيد المراجعة')
                                                <a href="/transfer-money-firm-order/debt/{{$transferMoneyFirmOrder->id}}" title="قبول كدين "  class="btn btn-sm btn-outline-success"><i class="icon-check" style="font-size:19px"></i></a>
                                                <a href="/transfer-money-firm-order/reject/{{$transferMoneyFirmOrder->id}}" title="الغاء  "  class="btn btn-sm btn-danger"><i class="icon-close" style="font-size:19px"></i></a>
                                                <a href="/transfer-money-firm-order/accept/{{$transferMoneyFirmOrder->id}}" title="تم الدفع "  class="btn btn-sm btn-success"><i class="icon-check" style="font-size:19px"></i></a>
                                               @elseif($transferMoneyFirmOrder->status=='دين')
                                                    <a href="/transfer-money-firm-order/accept/{{$transferMoneyFirmOrder->id}}" title="تم الدفع "  class="btn btn-sm btn-success"><i class="icon-check" style="font-size:19px"></i></a>
                                            
                                                @else
                                                <a href="/transfer-money-firm-order/reject/{{$transferMoneyFirmOrder->id}}" title="قبول كدين "  class="btn btn-sm btn-danger"><i class="icon-close" style="font-size:19px"></i></a>
                                                                                        
                                                @endif
                                            </td>
                                        </tr>
                                       @endif
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




<!-------------create--------->
<div class="modal fade" id="createmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabelcreate">إضافة طلب شركة شحن جديدة</h4>
            </div>
            <div class="modal-body"> 
                <form method="Post" action="{{ route('transfer-money-firm-order.store') }}" enctype="multipart/form-data">

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="اسم شركة الشحن" name="transfer_money_firm_id" aria-label="transfer_money_firm_id" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="اسم المستخدم" name="user_id" aria-label="user_id" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="اسم المرسل" name="sender" aria-label="sender" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="القيمة" name="value" aria-label="value" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="العملة" name="currency" aria-label="currency" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="رقم الوثيقة" name="dekont_no" aria-label="dekont_no" aria-describedby="basic-addon2">

                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="كلمة المرور" name="password" aria-label="password" aria-describedby="basic-addon2">

                    </div>

                    <input type="hidden" name="transfer_money_firm_id" value="1" />
                    <input type="hidden" name="user_id" value="1" />
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="modal-footer">   
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="#" class="btn btn-secondary" data-dismiss="modal">الغاء الأمر</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--------------delete -------------->
@foreach ($transferMoneyFirmOrders as $key => $transferMoneyFirmOrder)
<div class="modal fade" id="deleteModal{{$transferMoneyFirmOrder->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">هل أنت بالتاكيد تريد الحذف </h4>
            </div>
            <div class="modal-body"> 
              <form action="{{ route('transfer-money-firm-order.destroy', $transferMoneyFirmOrder->id) }}" method="POST">
               @csrf
               @method('DELETE')
               <input type="hidden" name="_token" value="{{ csrf_token() }}" />
               <!-- <input type="hidden" name="transfer_money_firm" value="1" /> -->

               <div class="modal-footer">
                   <button type="submit" class="btn btn-primary">نعم</button>
                   <a href="#" class="btn btn-secondary" data-dismiss="modal">الغاء الأمر</a>
               </div>
              </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<!--------------edit -------------->
@foreach ($transferMoneyFirmOrders as $key => $transferMoneyFirmOrder)
<div class="modal fade" id="editModal{{$transferMoneyFirmOrder->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeledit">تعديل معلومات طلب شركة شحن جديدة</h4>
            </div>
            <div class="modal-body"> 
                <form method="POST" action="{{ route('transfer-money-firm-order.update',  $transferMoneyFirmOrder->id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->transfer_money_firm_name}}" required placeholder="اسم شركة الشحن" name="transfer_money_firm_id" aria-label="transfer_money_firm_id" aria-describedby="basic-addon2" readonly>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->user_name}}" required placeholder="اسم المستخدم" name="user_id" aria-label="user_id" aria-describedby="basic-addon2" readonly>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->sender}}" required placeholder="اسم المرسل" name="sender" aria-label="sender" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->value}}" required placeholder="القيمة" name="value" aria-label="value" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->currency}}" required placeholder="العملة" name="currency" aria-label="currency" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->dekont_no}}" required placeholder="رقم الوثيقة" name="dekont_no" aria-label="dekont_no" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$transferMoneyFirmOrder->password}}" required placeholder="كلمة المرور" name="password" aria-label="password" aria-describedby="basic-addon2">
                    </div>
                    
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <!-- <input type="hidden" name="transfer_money_firm" value="1" /> -->

                    <div class="modal-footer"> 
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="#" class="btn btn-secondary" data-dismiss="modal">الغاء الأمر</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach


@foreach ($transferMoneyFirmOrders as  $user)
<div class="modal fade" id="balanceModal{{$user->user_id}}" tabindex="-1" role="dialog">
 <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabel"> اضافة رصيد   </h4>
            </div>
            <div class="modal-body"> 
            <form method="POST" action="{{ route('users.balance') }}" enctype="multipart/form-data">
            {{ csrf_field() }}
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>
                        <input type="text" class="form-control" required placeholder=" القيمة بدون كتابة TL"  aria-label=" "  name ="value" aria-describedby="basic-addon1">
                    </div>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>

                        <select class="custom-select" required name="payment_done" id="role">
                                  <option selected value="1">  مدفوع </option>
                                   <option value="0">دين</option>
                               
                                </select>  


                    </div>
                    <input class="form-control" require aria-label=" "  type="hidden"  name ="agent_id" value="{{$user->user_id}}" aria-describedby="basic-addon1">
                 
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="modal-footer"> 
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="#"  data-dismiss="modal" class="btn btn-secondary">الغاء الأمر</a>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endforeach

@endsection