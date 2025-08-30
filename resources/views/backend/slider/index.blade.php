
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
               <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2> السلايدر</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active"> </li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="javascript:void(0);" data-toggle="modal" class="btn btn-primary" data-target="#createmodal" ><i class="fa fa-add">أضف  جديد</i></a>
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
                          @if($type=="slide")
                            <h2>معرض الصور</h2>
                          @else
                          <h2>الاخبار </h2>
                          @endif
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                         @if($type=="slide")
                                               <th>الصورة </th>
                                          @else
                                             <th>الخبر </th>
                                          @endif
                                            
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sliders as $key => $slider)
                                        <tr>
                                            <td>
                                                 @if($type=="slide")
                                                  <img src="{{ asset('assets/images/sliders/' . $slider->image) }}" alt="{{$slider->title}}" data-toggle="tooltip" data-placement="top" title="{{$slider->title}}" class="width35 rounded"></td>

                                          @else
                                             {{$slider->title}} 
                                          @endif
                                            <td class="project-actions">
                                                <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal">
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$slider->id}}" class="btn btn-sm btn-outline-danger" ><i class="icon-trash"></i></a>
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

<!-------------create--------->
<div class="modal fade" id="createmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabelcreate">أضف عنصر جديد</h4>
            </div>
            <div class="modal-body"> 
                <form method="Post" action="{{ route('slider.store') }}" enctype="multipart/form-data">
                    

                  
                         @if($type=="slide")
                             <div class="input-group mb-3">
                          <div class="input-group-prepend">
                              <span class="input-group-text">تحميل</span>
                          </div>
                         <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image">
                            <label class="custom-file-label" for="inputGroupFile01">اختر الصورة</label>
                        </div>
                    </div>
                         @else
                                             <div class="input-group mb-3">
                          <div class="input-group-prepend">
                              <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                          </div>
                         <input type="text" class="form-control" required placeholder="نص الخبر " name="title" aria-label="title" aria-describedby="basic-addon2">
                         </div>
                           @endif
                           @if($type=="slide")
                            <input type="hidden" class="form-control"  required name="is_news" value="0" aria-describedby="basic-addon2">
                           @else
                          <input type="hidden" class="form-control" required name="is_news" value="1" aria-describedby="basic-addon2">
                          @endif
                                
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
@foreach ($sliders as $key => $slider)
<div class="modal fade" id="deleteModal{{$slider->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">هل بالتاكيد تريد الحذف</h4>
            </div>
            <div class="modal-body"> 
              <form action="{{ route('slider.destroy', $slider->id) }}" method="POST">
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
@foreach ($sliders as $key => $slider)
<div class="modal fade" id="editModal{{$slider->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeledit">تعديل معلومات الصورة </h4>
            </div>
            <div class="modal-body"> 
                <form method="POST" action="{{ route('slider.update', ['slider' => $slider->id]) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                   
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>
                        <input type="text" class="form-control" value="{{$slider->title}}" required placeholder="الاسم" name="title" aria-label="title" aria-describedby="basic-addon2">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">الصورة</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image">
                            <label class="custom-file-label" for="inputGroupFile01"></label>
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


@endsection