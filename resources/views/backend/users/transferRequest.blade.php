    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>    رصيد الزبائن </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
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
                            <h2>   شحن الارصدة </h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                                                
                                            <th>اسم الزبون</th>
                                            <th>  القيمة الاساسية</th>
                                             <th>المبلغ المتبقي</th>
                                            <th>تاريخ الاجراء</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                        <tr>
                                           <td class="project-title">
                                             <h6> {{ $transaction->receiver ? $transaction->receiver->name : 'غير معروف' }} </h6>
                                            </td>
                                            <td>{{ $transaction->amount }}{{ $transaction->currency }}</td>
                                            <td>{{ $transaction->remain_amount }}{{ $transaction->currency }}</td>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>@if($transaction->payment_done==1)
                                               <h6 style="color:green">   تم الدفع  </h6>
                                               @elseif($transaction->payment_done==-1)
                                                <h6 style="color:red">     الغاء</h6>
                                                @else
                                                <h6 style="color:red">        دين</h6>
                                                @endif

                                            </td>

                                            <td class="project-actions">
                                  
                                            @if(!$transaction->payment_done)
                                            <a href="/users/transactions/done/{{$transaction->id}}" title="تم تسديد الدين "  class="btn btn-sm btn-success"><i class="icon-check" style="font-size:19px"></i></a>
<a href="javascript:void(0);"   title="دفعة جزئية  "   class="btn btn-sm btn-warning" data-toggle="modal" data-target="#paymentModal{{$transaction->id}}">  <i class="icon-check" style="font-size:19px"></i></a>
                                              @endif
                                        </td>
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



<!--------------payment -------------->
@foreach ($transactions as $key => $transaction)
<div class="modal fade" id="paymentModal{{ $transaction->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">اكتب القيمة التي تم دفعها</h4>
            </div>
            <div class="modal-body"> 
              <form action="{{ url('users/transactions/partial/done/' . $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-footer">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
 <input 
        type="number" 
        step="1" 
        class="form-control" 
        required 
        placeholder="المبلغ المدفوع" 
        name="amount" 
        max="{{ $transaction->amount }}" 
        oninput="if (parseFloat(this.value) > {{ $transaction->amount }}) this.value = {{ $transaction->amount }};">
                    </div>
                    <button type="submit" class="btn btn-primary">تأكيد الدفع</button>
                    <a href="#" class="btn btn-secondary" data-dismiss="modal">إلغاء</a>
                </div>
              </form>
            </div>
        </div>
    </div>
</div>
@endforeach




@endsection