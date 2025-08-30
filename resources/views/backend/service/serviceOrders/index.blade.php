
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
               <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2> خدمة السرفر </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">السرفر</li>
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
                            <h2>التطبيقات</h2>
                        </div>
               <div class="body project_report">
    <div class="table-responsive">
        <table class="table table-hover js-basic-example dataTable table-custom mb-0">
            <thead>
                <tr>
                       <th> صاحب الطلب </th> 
                        <th>الخدمة المطلوبة </th> 
                        <th>تاريخ الطلب  </th> 
                     <th> السعر </th>
                       <th>سبب الرفض</th>
                        <th>الحالة</th>
                    <th>العمليات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serviceOrders as $key => $serviceOrder)
                <tr>
    <td class="project-title">
        <h6>{{$serviceOrder->user_name}}</h6>
    </td>
<td class="project-title">
    <span style="display: none;">{{ \Carbon\Carbon::parse($serviceOrder->created_at)->timestamp }}</span>
    <h6>{{ \Carbon\Carbon::parse($serviceOrder->created_at)->format('Y-m-d H:i:s') }}</h6>
</td>
    @if($serviceOrder->service_name)
        <td>{{$serviceOrder->service_name}}</td>
    @else
        <td>****</td>
    @endif
     <td class="project-title">
        <h6>{{$serviceOrder->price}}TL</h6>
    </td>
      @if($serviceOrder->reject_reason)
        <td>{{$serviceOrder->reject_reason}}</td>
    @else
        <td>****</td>
    @endif
    @if($serviceOrder->status==1)
        <td>قيد الانتظار</td>
    @elseif($serviceOrder->status==2)
        <td>تمت بنجاح </td>
     @else 
        <td> الغاء</td>
    @endif
    <td class="project-actions">
        <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal"></a>
     <a href="javascript:void(0);" data-toggle="modal" data-target="#viewModal{{$serviceOrder->id}}"class="btn btn-sm btn-outline-primary"><i class="icon-eye"></i></a>
  
      <a href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$serviceOrder->id}}" class="btn btn-sm btn-outline-danger"><i class="icon-trash"></i></a>
     
      @if($serviceOrder->status==1)
        <a href="javascript:void(0);" data-toggle="modal" data-target="#rejectModal{{$serviceOrder->id}}"title="رفض الطلب" class="btn btn-sm btn-danger"><i class="icon-close" style="font-size:19px"></i></a>
        <a href="/service-order/accept/{{$serviceOrder->id}}" title="قبول الطلب" class="btn btn-sm btn-success"><i class="icon-check" style="font-size:19px"></i></a>
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

<!--------------Reject -------------->
@foreach ($serviceOrders as $key => $serviceOrder)
<div class="modal fade" id="rejectModal{{$serviceOrder->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">اكتب سبب الرفض للتوضيح للزبون من فضلك</h4>
            </div>
            <div class="modal-body"> 
              <form action="{{ route('service-order.reject', $serviceOrder->id) }}" method="POST">
               @csrf <!-- هذه الحماية لمنع CSRF -->
               <div class="modal-footer">
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="اكتب شيئا " name="reject_reason">
                    </div>
                   <button type="submit" class="btn btn-primary">نعم</button>
                   <a href="#" class="btn btn-secondary" data-dismiss="modal">الغاء الأمر</a>
               </div>
              </form>
           </div>
        </div>
    </div>
</div>
@endforeach



<!-------------create--------->
<div class="modal fade" id="createmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabelcreate">إضافة طلب جديد</h4>
            </div>
            <div class="modal-body"> 
                <form method="Post" action="{{ route('turkification-order.store') }}" enctype="multipart/form-data">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="IME"  name="ime" aria-label="ime" aria-describedby="basic-addon2">
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" name="user_id" value="1" />
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
@foreach ($serviceOrders as $key => $serviceOrder)
<div class="modal fade" id="deleteModal{{$serviceOrder->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">هل أنت بالتاكيد تريد الحذف </h4>
            </div>
            <div class="modal-body"> 
              <form action="{{ route('service-order.destroy', $serviceOrder->id) }}" method="POST">
               @csrf
               @method('DELETE')
               <input type="hidden" name="_token" value="{{ csrf_token() }}" />
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
@foreach ($serviceOrders as $key => $serviceOrder)
<div class="modal fade" id="viewModal{{$serviceOrder->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeledit">  معلومات الطلب: </h4>
            </div>
            <div class="modal-body"> 
       

                    <div class="input-group mb-3">
                 
                      <p style="width:100%">  <b>اسم الزبون : </b> {{$serviceOrder->user_name}} </p>
                      <p style="width:100%">  <b>الخدمة  : </b> {{$serviceOrder->service_name}} </p>
                      <p style="width:100%">  <b>السعر  : </b> {{$serviceOrder->price}}TL </p>
                   @if($serviceOrder->ime)
                   <p style="width:100%">  <b>ime  : </b> {{$serviceOrder->ime}} </p> 
                   @endif 
                    @if($serviceOrder->uuid)
                    <p style="width:100%">  <b>رقم العملية  : </b> {{$serviceOrder->uuid}} </p>                    
                   @endif 
                   @if($serviceOrder->status==1)
                   <p style="width:100%">  <b>الحالة  : </b> قيد المراجعة </p> 
                   @elseif ($serviceOrder->status==3)
                  <p style="width:100%">  <b>الحالة  : </b> الغاء  </p>  
                      @else
                   <p style="width:100%">  <b>الحالة  : </b>  تمت بنجاح </p>     
                   @endif    
                   @if($serviceOrder->email)
                  <p style="width:100%">  <b>ايميل  : </b> {{$serviceOrder->email}} </p>         
                   @endif 
                   @if($serviceOrder->username)
                   <p style="width:100%">  <b>اسم المستخدم  : </b> {{$serviceOrder->username}} </p>               
                   @endif 
                   @if($serviceOrder->password)
                   <p style="width:100%">  <b>كلمة السر  : </b> {{$serviceOrder->password}} </p>                   
                   @endif 
                   @if($serviceOrder->count)
                  <p style="width:100%">  <b>العدد  : </b> {{$serviceOrder->count}}   </p>                  
                   @endif 
                   @if($serviceOrder->note)
                   <p style="width:100%">  <b>ملاحظة  : </b> {{$serviceOrder->note}}<br> </p>                 
                   @endif 
                 
                   @if($serviceOrder->reject_reason)
                   <p style="width:100%">  <b>سبب الرفض  : </b> {{$serviceOrder->reject_reason}}  </p>     
                   @endif 
                       
               
                      @if($serviceOrder->mobile)
                   <p style="width:100%">  <b>رقم الهاتف الذي سيرسل الصور من خلاله عبر الواتس اب : </b> {{$serviceOrder->mobile}} </p>                     
                   @endif 
                     @if($serviceOrder->last_mobile)
                   <p style="width:100%">  <b>رقم الهاتف للبدل الضائع : </b> {{$serviceOrder->last_mobile}} </p>                     
                   @endif
                      
                      
                   
                    </div>
  
            
   
              
              
                
            </div>
        </div>
    </div>
</div>
@endforeach
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">

<script>
  
  $('.js-basic-example').DataTable({
    "ordering": false // تعطيل الترتيب لجميع الأعمدة
});
</script>
@endsection