    
    @extends('backend.layout.app')



@section('content')
    
    
    <!-- mani page content body part -->
    <div id="main-content">
        <div class="container-fluid">
       
        <div class="block-header">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <h2>لوحة التحكم</h2>
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
            </div> 
             <div class="row clearfix">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="body">
                            <div class="currency_state d-flex">
                                <div><img src="assets/images/coin/BTC.svg" width="40"></div>
                                <div class="ml-3">
                                    <div class="name">الرصيد الحالي</div>
                                    <h5 class="mb-0">{{$balance}} TL</h5>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
             </div>
                    
            <div class="row clearfix">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="body">
                            <div class="currency_state d-flex">
                                <div><img src="assets/images/coin/BTC.svg" width="40"></div>
                                <div class="ml-3">
                                    <div class="name">الصادر</div>
                                    <h5 class="mb-0">{{ number_format($financials['outgoing'], 2) }} TL</h5>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="body">
                            <div class="currency_state d-flex">
                                <div><img src="assets/images/coin/BTC.svg" width="40"></div>
                                <div class="ml-3">
                                    <div class="name">الورد</div>
                                    <h5 class="mb-0"> {{ number_format($financials['incoming'], 2) }} TL</h5>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="body">
                            <div class="currency_state d-flex">
                                <div><img src="assets/images/coin/BTC.svg" width="40"></div>
                                <div class="ml-3">
                                    <div class="name">الارباح</div>
                                    <h5 class="mb-0">{{ number_format($financials['profitTotals'], 2) }}TL</h5>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="body">
                            <div class="currency_state d-flex">
                                <div><img src="assets/images/coin/BTC.svg" width="40"></div>
                                <div class="ml-3">
                                    <div class="name">المدين</div>
                                    <h5 class="mb-0">{{ number_format($financials['debts'], 2) }}TL</h5>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
                
            </div>  
        <div class="row clearfix row-deck">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">عدد التطبيقات</span>
                            <h4 class="mb-0 mt-2">{{$appRecords}}</h4>
                            <small class="text-muted">************</small>
                        </div>
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#39afa6" data-fill-Color="#73cec7">4,1,5,2,7,3,4</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">عدد الألعاب</span>
                            <h4 class="mb-0 mt-2">{{$gameRecords}}</h4>
                            <small class="text-muted">************</small>
                        </div>
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#ffa901" data-fill-Color="#efc26b">1,4,2,3,6,2</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">عدد شركات الاتصالات</span>
                            <h4 class="mb-0 mt-2">{{$dataCommunicationRecords}}</h4>
                            <small class="text-muted">************</small>
                        </div>
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#38c172" data-fill-Color="#84d4a6">1,4,2,3,1,5</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">عدد البطاقات الرقمية</span>
                            <h4 class="mb-0 mt-2">{{$ecardRecords}}</h4>
                            <small class="text-muted">************</small>
                        </div>
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#226fd8" data-fill-Color="#7ea7de">1,3,5,1,4,2</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card top_widget">
                        <div class="body">
                            <div class="icon"><i class="fa fa-flag"></i> </div>
                            <div class="content">
                                <div class="text mb-2 text-uppercase">عدد المسخدمين </div>
                                <h4 class="number mb-0">{{$users}} <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4>
                                <small class="text-muted">************</small>
                            </div>
                            
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#ffa901" data-fill-Color="#efc26b">1,4,2,3,6,2</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card top_widget">
                        <div class="body">
                            <div class="icon"><i class="fa fa-users"></i> </div>
                            <div class="content">
                                <div class="text mb-2 text-uppercase">عدد شركات شحن الأموال</div>
                                <h4 class="number mb-0">{{$transferMoneyFirmRecords}} <span class="font-12 text-muted"><i class="fa fa-level-down"></i> 7%</span></h4>
                                <small class="text-muted">************</small>
                            </div>
                            
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#ffa901" data-fill-Color="#7ea7de">1,4,2,3,6,2</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card top_widget">
                        <div class="body">
                            <div class="icon"><i class="fa fa-user"></i> </div>
                            <div class="content">
                                <div class="text mb-2 text-uppercase">خدمات السرفر</div>
                                <h4 class="number mb-0">{{$serviceRecords}} <span class="font-12 text-muted"><i class="fa fa-level-down"></i> 4%</span></h4>
                                <small class="text-muted">********</small>
                            </div>
                            
                        <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%" data-height="50px"
                        data-line-Width="1" data-line-Color="#ffa901" data-fill-Color="#84d4a6">1,4,2,3,6,2</div>
                        </div>
                    </div>
                </div>
         
            </div>



          
         
            
            <div class="row clearfix">
               
            </div>
            
        </div>
    </div>

    @endsection