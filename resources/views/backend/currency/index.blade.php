    
@extends('backend.layout.app')

@section('content')

<!-- main page content body part -->
<div id="main-content">
    <div class="container-fluid">
        @include('includes.alert-message')
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>العملات </h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>                            
                        <li class="breadcrumb-item">لوحة التحكم</li>
                        <li class="breadcrumb-item active">العملات </li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="javascript:void(0);" data-toggle="modal" class="btn btn-primary" data-target="#createmodal" ><i class="fa fa-add">إضافة عملة جديدة</i></a>
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
                            <h2> العملات </h2>
                        </div>
                        <div class="body project_report">
                            <div class="table-responsive">
                                <table class="table table-hover js-basic-example dataTable table-custom mb-0">
                                    <thead>
                                        <tr>                                            
                                            <th>العملة </th>
                                            <th> الرمز</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($currencies as $key => $currency)
                                        <tr>
                                            <td class="project-title">
                                                <h6> {{$currency->name}}
                                                    
                                                
                                                </h6>
                                            </td>
                                            <td>{{$currency->code}}</td>
                                            <td class="project-actions">
                                                <a href="#defaultModal" data-toggle="modal" data-target="#defaultModal">
                                                <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary"><i class="icon-eye"></i></a>
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#editModal{{$currency->id}}" class="btn btn-sm btn-outline-success"><i class="icon-pencil"></i></a>
                                                <a  href="javascript:void(0);" data-toggle="modal" data-target="#deleteModal{{$currency->id}}" class="btn btn-sm btn-outline-danger" ><i class="icon-trash"></i></a>
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
                <h4 class="title" id="defaultModalLabelcreate">إضافة عملة جديدة</h4>
            </div>
            <div class="modal-body"> 
                <form method="Post" action="{{ route('currency.store') }}" enctype="multipart/form-data">
              
                    <div class="input-group mb-3">
                       
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>  
                      
                        <input type="text" class="form-control" required placeholder=" الاسم"  name="name" aria-label="commession_percent" aria-describedby="basic-addon2">

                    </div>
                
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>  
                        <input type="text" class="form-control" required placeholder= "الكود"  name="code" aria-label="code" aria-describedby="basic-addon2">
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
@foreach ($currencies as $key => $currency)
<div class="modal fade" id="deleteModal{{$currency->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeldelete">هل أنت بالتاكيد تريد الحذف </h4>
            </div>
            <div class="modal-body"> 
             <form action="{{ route('currency.destroy', $currency->id) }}" method="POST">
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
@foreach ($currencies as $key => $currency)
<div class="modal fade" id="editModal{{$currency->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="title" id="defaultModalLabeledit">تعديل معلومات العملة </h4>
            </div>
            <div class="modal-body"> 
                <form method="POST"  action="{{ route('currency.update', $currency->id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                    <div class="input-group mb-3">
                       
                        <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>  
                      
                        <input type="text" class="form-control" required placeholder=" الاسم"  value="{{$currency->name}}" name="name" aria-label="commession_percent" aria-describedby="basic-addon2">
                      
                    </div>
                
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-edit"> </i></span>
                        </div>  
                        <input type="text" class="form-control" required placeholder=" الكود"  value="{{$currency->code}}" name="code" aria-label="code" aria-describedby="basic-addon2">
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