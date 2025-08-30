    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>قسم الخدمات العامة </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">  التصنيفات</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                   <!--     @if(auth()->user()->role==1)
                            <a href="javascript:void(0);" data-toggle="modal" class="btn btn-primary" data-target="#createmodal" ><i class="fa fa-add">أضف تصنيف جديد</i></a>
                       @endif-->
                        </div>
                        <div class="p-2 d-flex">
                        </div>
                    </div>
                </div>
            </div>    
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="header">
                            <h2>التصنيفات</h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                            <th>اسم  التصنيف</th>
                                            <th> الصورة </th>
                                            <th>العمليات</th>
                                            @if(auth()->user()->role==1)
                                            <th>نسبة الربح</th>
                                            <th>الحالة</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($services as $key => $service)
                                        <tr>
                                            <td class="project-title">
                                                <h6>{{$service->name}}</h6>
                                            </td>
                                            <td><img src="{{asset('assets/images/tweetcellSections/'.$service->image)}}" data-toggle="tooltip" data-placement="top" title="Team Lead" alt="Avatar" class="width35 rounded"></td>
                                          
                                            <td class="project-actions">
                                                <a href="/oyun/{{$service->id}}/category " class="btn btn-sm btn-outline-primary"><i class="icon-eye"></i></a>
                                               @if(auth()->user()->role==1)
                                              
                                                <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal">
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#editModal{{$service->id}}" class="btn btn-sm btn-outline-success"><i class="icon-pencil"></i></a>
                                                <a  href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$service->id}}" class="btn btn-sm btn-outline-danger" ><i class="icon-trash"></i></a>
                                                @endif
                                             </td>
                                            
                                            @if(auth()->user()->role==1)
 											 <td class="project-title">
                                                <h6>{{$service->increase_percentage}}%</h6>
                                             </td>
                                            <td>
                                            @if($service->status==1)
                                            <a href="javascript:void(0);" data-toggle="modal" class="btn btn-primary" data-target="#enableModal{{$service->id}}"style="background-color:#22a191" ><i class="fa fa-add" >ايقاف </i></a>
                                                @else
                                            <a href="javascript:void(0);" data-toggle="modal" class="btn btn-primary" data-target="#enableModal{{$service->id}}" style="background-color:#23b5a7a1"><i class="fa fa-add" >  تفعيل </i></a>

                                                @endif
                   
                                            </td>
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

<!-------------create--------->
<div class="modal fade" id="createmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" >إضافة تصنيف  جديد</h4>
            </div>
            <div class="modal-body"> 
                <form method="Post" action="{{ route('oyun-section.store') }}" enctype="multipart/form-data">

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" required placeholder="الاسم"  name="name" aria-label="name" aria-describedby="basic-addon2">
                    </div>
               
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">الصورة</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image">
                            <label class="custom-file-label" for="inputGroupFile01">اختر الصورة</label>
                        </div>
                    </div>
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
@foreach ($services as $key => $service)
<div class="modal fade" id="deleteModal{{$service->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" >هل أنت بالتاكيد تريد الحذف </h4>
            </div>
            <div class="modal-body"> 
             <form action="{{ route('oyun-section.destroy', $service->id) }}" method="POST">
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
@foreach ($services as $key => $service)
<div class="modal fade" id="editModal{{$service->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" >تعديل معلومات التصنيف </h4>
            </div>
            <div class="modal-body"> 
                <form method="POST" action="{{ route('oyun-section.update', $service->id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                 
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <select class="custom-select" required name="type" >
                                <option value="{{$service->type}}" >اختر نوع الخدمة</option>
                                <option value="1" >العاب</option>
                                <option value="2" >تطبيقات</option>
                                <option value="3" >بيانات واتصالات</option>
                                    
                        </select> 
                    </div>
                    <div class="input-group mb-3"> 
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$service->name}}" required placeholder="الاسم" name="name" aria-label="name" aria-describedby="basic-addon2">
                    </div>
                       <div class="input-group mb-3"> 
                        <div class="input-group-prepend">
                            <span class="input-group-text">نسبة الربح من السعر الاساسي(%)</span>
                        </div>
                        <input type="text" class="form-control" value="{{$service->increase_percentage}}" required placeholder="الاسم" name="increase_percentage" aria-label="name" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">الصورة</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image">
                            <label class="custom-file-label" for="inputGroupFile01">اختر الصورة </label>
                        </div>
                    </div>
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
@endforeach




<!--------------enable -------------->
@foreach ($services as $key => $service)
<div class="modal fade" id="enableModal{{$service->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                @if($service->status)
                <h4 class="title" id="defaultModalLabeldelete">هل أنت بالتاكيد تريد الغاء تفعيل الخدمة ؟ </h4>
                @else
                
                <h4 class="title" id="defaultModalLabeldelete">هل أنت بالتاكيد تريد تفعيل الخدمة؟  </h4>
                @endif
            </div>
            <div class="modal-body"> 
              <form action="/oyun-section/{{$service->id}}/status" method="POST">
               @csrf
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

@endsection