    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
               
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">  الخدمات</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
            
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
                            <h2>الباقات</h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                            <th>اسم  الباقة</th>
                                            <th> التصنيف </th>
                                            <th>السعر الاساسي </th>
                                            <th>السعر للبيع</th>
                                            @if(auth()->user()->role==1)
                                            <th>العمليات</th>
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
                                            <td> 

                                                <h6> 
                                                @if($service->type == 1)
                                                    tam
                                                @elseif($service->type == 2)
                                                    ses
                                                @elseif($service->type == 3)
                                                    FIRSAT SES
                                                @elseif($service->type == 4)
                                                    sms
                                                @elseif($service->type == 5)
                                                    Yds
                                                @elseif($service->type == 6)
                                                    3gCep
                                                @else
                                                *****
                                                @endif

                                                </h6>
                                            </td>
                                            <td class="project-title">
                                                <h6>{{$service->basic_price}}TL</h6>
                                            </td>
                                            <td class="project-title">
                                                <h6>{{$service->price}}TL</h6>
                                            </td>
                                          
                                            @if(auth()->user()->role==1)
                                            <td class="project-actions">
                                                <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal">
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#editModal{{$service->id}}" class="btn btn-sm btn-outline-success"><i class="icon-pencil"></i></a>
                                                <a  href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$service->id}}" class="btn btn-sm btn-outline-danger" ><i class="icon-trash"></i></a>
                                                  @if($service->is_firsat)
                                                  <a  href="/kontor/firsat/{{$service->id}}" class="btn btn-sm btn-outline-danger" style="background-color:pink !important" >🎁</a>

                                                  @else
                                                  <a  href="/kontor/firsat/{{$service->id}}" class="btn btn-sm btn-outline-danger" >🎁</a>
                                                  @endif

                                                  </td>
                                            <td>
                                            @if($service->status)
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


<!--------------delete -------------->
@foreach ($services as $key => $service)
<div class="modal fade" id="deleteModal{{$service->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" >هل أنت بالتاكيد تريد الحذف </h4>
            </div>
            <div class="modal-body"> 
             <form action="{{ route('kontor.destroy', $service->id) }}" method="POST">
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
                <h4 class="title" >تعديل معلومات اللعبة </h4>
            </div>
            <div class="modal-body"> 
                <form method="POST" action="{{ route('kontor.update', $service->id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <select class="custom-select" required name="section_id" >
                 
                               @foreach ($sections as $key => $section)
                                   @if( $service->section_id==$section->id)
                                <option value="{{$section->id}}" selected>{{$section->name}}</option>
                                    @else
                                <option value="{{$section->id}}" >{{$section->name}}</option>
                                    @endif
    
                               @endforeach
                        </select> 
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <select class="custom-select" required name="type" >
                    
                        <option value="1" @if($service->type == 1) selected @endif>tam</option>
                        <option value="2" @if($service->type == 2) selected @endif>ses </option>
                        <option value="3" @if($service->type == 3) selected @endif>FIRSAT SES </option>
                        <option value="4" @if($service->type == 4) selected @endif>sms</option>
                        <option value="5" @if($service->type == 5) selected @endif>Yds</option>
                        <option value="6" @if($service->type == 6) selected @endif>3gCep</option>
                       
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
                            <span class="input-group-text">السعر الاساسي</span>
                        </div>
                        <input type="text" class="form-control" required placeholder="السعر" value="{{$service->basic_price}}"  name="basic_price" aria-label="price" aria-describedby="basic-addon2">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">السعر للبيع</span>
                        </div>
                        <input type="text" class="form-control" required placeholder="السعر" value="{{$service->price}}"  name="price" aria-label="price" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <textarea class="form-control"  name="title"  placeholder="الوصف"  >{{$service->title}}</textarea>
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
              <form action="/kontor/{{$service->id}}/status" method="POST">
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