@extends('layouts.booking')

@section('content')
<style type="text/css">
  /* Start by setting display:none to make this hidden.
   Then we position it in relation to the viewport window
   with position:fixed. Width, height, top and left speak
   for themselves. Background we set to 80% white with
   our animation centered, and no-repeating */
.modal {
    display:    none;
    position:   fixed;
    z-index:    1000;
    top:        0;
    left:       0;
    height:     100%;
    width:      100%;
    background: rgba( 255, 255, 255, .8 ) 
                url('http://i.stack.imgur.com/FhHRx.gif') 
                /*url('{{asset('/assets/images/loader.gif')}}')*/
                50% 50% 
                no-repeat;
}

/* When the body has the loading class, we turn
   the scrollbar off with overflow:hidden */
body.loading {
    overflow: hidden;   
}

/* Anytime the body has the loading class, our
   modal element will be visible */
body.loading .modal {
    display: block;
}
dd{
  text-align: right !important;
}
</style>

    <!-- Marketing messaging and featurettes
    ================================================== -->
    <!-- Wrap the rest of the page in another container to center all the content. -->

    <div class="container marketing">

      <!-- Three columns of text below the carousel -->

      <form action="#" method="post" id="dateForm" class="form-horizontal">
        <div class="row">
          <div class="col-lg-12">
            <h2>Reserver un cyclo</h2>
          </div>
        </div>
       <!-- <div class="row">
          <div class="col-lg-6">
              <div class="well well-sm">
                <h2 id="amt1" class="rule_amount">0 € / jour</h2>
              </div>
          </div>
        </div>
      -->
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label for="" class="col-sm-2 control-label">Cyclo</label>
              <div class="col-sm-10">
                <select id="selec" name="selectCY" class="form-control" required="required">
                  <option disabled="" selected="" value=""> -- Sélectionner Cyclo -- </option>
                   <!-- @foreach ($cyclos as $cyclo)
                      @if($cyclo->agent == '0')
                        <option id="{{$cyclo->id}}" value="{{$cyclo->id}}"> {{ $cyclo->name }} </option>
                      @endif  
                    @endforeach -->
                  @foreach ($cyclos as $cyclo)
                    @if($cyclo-> cy_type == '0')
                      <option id="{{$cyclo->id}}" value="{{$cyclo->id}}" selected=""> {{ $cyclo->name }} </option>
                    @else
                      <option id="{{$cyclo->id}}" value="{{$cyclo->id}}"> {{ $cyclo->name }} </option>
                    @endif
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>        

        <div class="row">
          <div class="col-lg-6">
            <p><div id="ucalendar"></div></p>
          </div>
        </div>

        <div class="modal" id="ajax-loading">
          <!-- jquery loader -->
        </div>

        <div id="validation-errors" style="display: none"></div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="start_date" class="col-sm-3 control-label">Date</label>
              <div class="col-sm-4">
                <input type="text" class="form-control"  name="start_date" id="start_date" value="" placeholder="" required="required" readonly="">
              </div>
            </div>
          </div> 
        </div>          
        <div class="row">
          <div class="col-md-3">
            <div id="amt_msg"></div>
            <h2 id="amt" class="rule_amount" style="display: none !important;">0 € / jour</h2>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <h3>Mes coordonnées</h3>
            <div class="form-group">
              <label for="surname" class="col-sm-2 control-label">Prénom</label>
              <div class="col-sm-8">
                {{ Form::text('surname',  $user->surname,array('required'=>'','class'=>'form-control','id'=>'surname','placeholder'=>'Prénom')) }}
              </div>
            </div>

            <div class="form-group">
              <label for="name" class="col-sm-2 control-label">Nom</label>
              <div class="col-sm-8">
                {{ Form::text('name', $user->name,array('required'=>'','class'=> 'form-control','id'=>'name','placeholder'=>'Nom')) }}
              </div>
            </div>

            <div class="form-group">
              <label for="address" class="col-sm-2 control-label">Adresse</label>
              <div class="col-sm-8">
                {{ Form::text('address', $user->address,array('class'=> 'form-control','id'=>'address','placeholder'=>'Adresse')) }}
              </div>
            </div>

            <div class="form-group">
              <label for="city" class="col-sm-2 control-label">Ville</label>
              <div class="col-sm-8">
                {{ Form::text('city', $user->city,array('class'=> 'form-control','id'=>'city','placeholder'=>'Ville')) }}
              </div>
            </div>
            
            <div class="form-group">
              <label for="email" class="col-sm-2 control-label">Adresse Mail</label>
              <div class="col-sm-8">
                {{ Form::email('email', $user->email,array('required'=>'','class'=> 'form-control','id'=>'email','placeholder'=>'Adresse Mail')) }}
              </div>
            </div>
            
            <div class="form-group">
              <label for="phone" class="col-sm-2 control-label">N° de tel</label>
              <div class="col-sm-8">
                {{ Form::text('phone', $user->phone,array('required'=>'','class'=> 'form-control','id'=>'phone','placeholder'=>'N° de tel', 'maxlength' => '10')) }}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <h3>Paiement</h3>
            <div class="panel-group" id="accordion">
              <div class="panel panel-default">
              <div class="panel-heading">
              <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#choosecard">Carte existante</a>
              </h4>
              </div>
              <div id="choosecard" class="panel-collapse collapse">
              <div class="panel-body">
              @if (!empty($cards)) 
              @foreach ( $cards as $card)
              {{ Form::radio('card', $card->id, true, array('id'=>$card->id))}}
              {{ Form::label($card->id, "xxxx-xxxx-xxxx-".$card->last4)}}
              <br>
              @endforeach
              @endif
              </div>
              </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Nouvelle carte</a>
                  </h4>
                </div>
                <div id="collapseThree" class="panel-collapse collapse">
                  <div class="panel-body">
                    <div class="form-group">
                      <label for="cardnum" class="col-sm-3 control-label">Carte</label>
                      <div class="col-sm-9">
                        {{ Form::text('cardnum', null,array('class'=> 'form-control','id'=>'cardnum','placeholder'=>'Carte','maxlength' => 16)) }}
                      <label id="cardnum-error" class="error" for="cardnum" style="display: none;"></label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="month" class="col-sm-3 control-label">Mois</label>
                      <div class="col-sm-3">
                        {{Form::selectRange('month', 1, 12, 1, array('class' => 'form-control', 'id'=>'month'))}}
                        <label id="month-error" class="error" for="month" style="display: none;"></label>
                      </div>
                      <label for="year1" class="col-sm-3 ">Année</label>
                      <div class="col-sm-3">
                        {{Form::selectRange('year1', date('Y'), (intval(date("Y")) + 50), date('Y'), array('class' => 'form-control', 'id'=>'year1'))}}
                        <label id="year1-error" class="error" for="year1" style="display: none;"></label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="cvc" class="col-sm-3 control-label">CVC</label>
                      <div class="col-sm-2">
                        {{ Form::text('cvc', null,array('class'=> 'form-control','id'=>'cvc','placeholder'=>'cvc','maxlength' => 3)) }}
                        <label id="cvc-error" class="error" for="cvc" style="display: none;"></label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>  
            </div>

            <div>
              <label>Avez-vous un coupon?</label>
              <input type="text" name="coupon" id="coupon">
              <button type="button" id="coupon_btn">Appliquer un coupon</button>
              <p id="disc_val"></p>
              {{ Form::hidden('discounted', null, array('id' => 'discounted')) }}
              {{ Form::hidden('disc_in_percent', null, array('id' => 'disc_in_percent')) }}
              {{ Form::hidden('applied', 'false', array('id' => 'applied')) }}
            </div>         
          </div>             
        </div>

            <div class="row">
              <div class="col-lg-6">
                  
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                      <label style="text-align: justify;">
                        <input type="checkbox" name="agr0" id="agr0" required="required"> Je certifie être inscrit au registre des auto-entrepreneurs ou des entrepises individuelles et être à jour de mes cotisations et déclarations sociales.<br>
                        Je m'engage à respecter strictement le code de la route. J'atteste avoir été formé à la conduite et à la sécurité (la mienne, celle des tiers et celle des passagers). J'ai compris qu'en cas d'infraction aux règles de sécurité et au code de la route, je serais seul responsable et pourrais ne pas être couvert pas l'assurance proposée par Cyclopolitain.<br>
                        J'ai lu et j'accepte les conditions générales <a target="_blank" href="{{url('/cgv')}}">conditions générales de location</a> . J'accepte notamment qu'une pré-autorisation de paiement de 150€ soit concédée en garantie au loueur.  
                      </label><br>                       
                        <label id="agr0-error" for="agr0" class="error" style="display: none; font-weight: 700 !important; font-weight: 700 !important;">Ce champ est requis.</label>
                    </div>
                  </div>
                </div>

                
          
                <input type="hidden" name="additional_book" id="additional_book" value="">
                <div class="form-group">
                  <div class="col-sm-offset-4 col-sm-10">
                    <div class="checkbox">
                      <button type="button" class="btn btn-primary" id="pay">PAYER ET RESERVER</button>
                      <!-- <button type="button" id="pay_additional" class="btn btn-primary">FAIRE UNE AUTRE RESERVATION</button> -->
                    </div>
                  </div>
                </div>

              </div>
            </div>


      </form>

  </div><!-- /.container -->
 
@stop
@section('footerjs')
<script src="{{asset('/assets/fullcalendar/lib/moment.min.js')}}"></script>
<script src="{{asset('/assets/fullcalendar/fullcalendar.js')}}"></script>
<script src="{{asset('/assets/fullcalendar/lang-all.js')}}"></script>
<script src="{{asset('/assets/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script type="text/javascript" src= "{{asset('/assets/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
<script type="text/javascript">
  var url_id;
  var end;
  var clickdate; 
  var starT;
  var form=$("#dateForm");
  $body = $("body");
  $(document).on({
    ajaxStart: function() { $body.addClass("loading"); },
    ajaxStop: function() { $body.removeClass("loading"); }    
  });
  var $form = $('#dateForm');
  var $checkbox = $('#agr0');
  
  var previous_cell = null;
  var previous_cell1 = null;
  $form.on('submit', function(e) {
    if(!$checkbox.is(':checked') ) {
      e.preventDefault();
      alert('S\'il vous plaît accepter les termes et conditions!');
    }
  });

  $('#ucalendar').fullCalendar({    
    lang: 'fr',
  });

  function price(day1, day2, cyc){    
    if( day1 && day2 ){
      $.ajax({
        type: "POST",
        url: "prix",
        data: { start_time: day1, end_time: day2, cyc: cyc },
        success: function (resp){
          $('#amt').html("");
          $('#amt1').html("");
          $('#amt').html(resp.data.total+" € / jour");
          $('#amt1').html(resp.data.total+" € / jour");
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
          alert("Error: " + XMLHttpRequest.responseJSON.errors);
        }
      });
    }
  }
  //new changes
  $(document).ready(function(){
    url_id = $('#selec').val();
    $('#amt').html(" 0€ / jour");            
    $('#amt1').html(" 0€ / jour");
    $('#amt_msg').html('');
    var buku = "book/"+url_id;
    $("#dateForm").attr('action',buku); //form submission action
    $('#start_date').val("");
    if(url_id == null || url_id == '0' || url_id == undefined ){
      console.log('err');
    } else{      
      $.ajax({
        type: "POST",
        url: "availability/"+url_id,
        success: function (response) {
          // console.log(response);
          var dates = response.dates;
          var today = moment().format('YYYY-MM-DD');
          $('#ucalendar').fullCalendar('destroy');
          var calendar = $('#ucalendar').fullCalendar({
            lang : 'fr',
            viewRender: function(view,element) {//restricting available dates to 2 moths in future
              var now = new Date();
              var end = new Date();
              var start = new Date();
              end.setMonth(now.getMonth() + 6); //Adjust as needed
              start.setMonth(now.getMonth()); //Adjust as needed
              if ( end < view.end) {
                $("#ucalendar .fc-next-button").hide();
                return false;
              }else {
                $("#ucalendar .fc-next-button").show();
              }

              if ( view.start < start) {
                $("#ucalendar .fc-prev-button").hide();
                return false;
              }else {
                $("#ucalendar .fc-prev-button").show();
              }
            },
            dayRender: function (date, cell) {
              if( ((date.format("YYYY-MM-DD") in dates) && dates[date.format("YYYY-MM-DD")] <= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
                cell.css('background-color', '#f0c2c2'); //unavailable due to vacation or passed day or all quantity used
              }else cell.css('background-color', '#c2dfd0');
              /*if (($.inArray(date.format("YYYY-MM-DD"), dates) >= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
                cell.css("background-color", "#F5F5F5");
              }else cell.css("background-color", "#BBDEFB");*/
            },
            dayClick: function(date, jsEvent, view) {
              /*if ($.inArray(date.format("YYYY-MM-DD"), dates) < 0 &&  date.format("YYYY-MM-DD") >= today){*/
              if( ((date.format("YYYY-MM-DD") in dates) && dates[date.format("YYYY-MM-DD")] <= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
                console.log('unavailable!!');
              }else{
                if(previous_cell != null) $(previous_cell).css('background-color', '#c2dfd0');
                $(this).css('background-color', '#eee');
                previous_cell = $(this);
                // console.log(previous_cell)
                clickdate = date.format("YYYY-MM-DD");              
                $('#start_date').val(clickdate);
                // $('#end_datetime').val(clickdate);
                price(clickdate, clickdate, url_id);
                var wee = new Date(clickdate);
                var weekend = wee.getDay();
                $.ajax({
                  type: 'POST',
                  url: 'pricedesc/'+url_id,
                  data:{ st_date: clickdate},
                  success: function(res){
                    $('#amt_msg').html(res.result);
                    // console.log(res);
                  },
                  error: function(XMLHttpRequest, textStatus, errorThrown) { 
                    alert("Error: " + XMLHttpRequest.responseJSON.error);
                  } 
                });
              }
            },
          });      
        },
        error:function(XMLHttpRequest, textStatus, errorThrown) {
          alert("Error: " + XMLHttpRequest.responseJSON.errors);
        },
      });
    }
  });
  //new changes
  $('#selec').on('change', function ( e ) {
    url_id = this.value;
    $('#amt').html(" 0€ / jour");            
    $('#amt1').html(" 0€ / jour");
    $('#amt_msg').html('');
    var buku = "book/"+url_id;
    $("#dateForm").attr('action',buku); //form submission action
    e.preventDefault();
    $('#start_date').val("");

    $.ajax({
      type: "POST",
      url: "availability/"+url_id,
      success: function (response) {
          // console.log(response);
        $('#start_date').val("");
        // $('#end_datetime').val("");
        var yday = new Date(Date.now() - 86400000);
        var yesterday = moment(yday).format('YYYY-MM-DD 23:59:59');
        var dates = response.dates;
        var lastDate = $(dates).get(-1);
        var today = moment().format('YYYY-MM-DD');
        var range=[];
        $.each(dates, function(index, value){
          if(!moment(value, 'YYYY-MM-DD').isBefore(today)){
            range.push(value);
          }
        });

        $('#ucalendar').fullCalendar('destroy');
        var calendar = $('#ucalendar').fullCalendar({
          lang: 'fr',
          viewRender: function(view,element) {//restricting available dates to 2 moths in future
            var now = new Date();
            var end = new Date();
            var start = new Date();
            end.setMonth(now.getMonth() + 6); //Adjust as needed
            start.setMonth(now.getMonth()); //Adjust as needed
            if ( end < view.end) {
              $("#ucalendar .fc-next-button").hide();
              return false;
            }else {
              $("#ucalendar .fc-next-button").show();
            }

            if ( view.start < start) {
              $("#ucalendar .fc-prev-button").hide();
              return false;
            }else {
              $("#ucalendar .fc-prev-button").show();
            }
          },
          dayRender: function (date, cell) {
            if( ((date.format("YYYY-MM-DD") in dates) && dates[date.format("YYYY-MM-DD")] <= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
              cell.css('background-color', '#f0c2c2'); //unavailable due to vacation or passed day or all quantity used
            }else cell.css('background-color', '#c2dfd0');
            /*if (($.inArray(date.format("YYYY-MM-DD"), dates) >= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
              cell.css("background-color", "#F5F5F5");
            }else cell.css("background-color", "#BBDEFB");*/
          },
          dayClick: function(date, jsEvent, view) {
            /*if ($.inArray(date.format("YYYY-MM-DD"), dates) < 0 &&  date.format("YYYY-MM-DD") >= today){*/
            if( ((date.format("YYYY-MM-DD") in dates) && dates[date.format("YYYY-MM-DD")] <= 0 && date.format("YYYY-MM-DD") >= today) || date.format("YYYY-MM-DD") < today){
              console.log('unavailable!!');
            }else{
              if(previous_cell1 != null) $(previous_cell1).css('background-color', '#c2dfd0');
              $(this).css('background-color', '#eee');
              previous_cell1 = $(this);
              clickdate = date.format("YYYY-MM-DD");              
              $('#start_date').val(clickdate);
              // $('#end_datetime').val(clickdate);
              price(clickdate, clickdate, url_id);
              var wee = new Date(clickdate);
              var weekend = wee.getDay();
              $.ajax({
                type: 'POST',
                url: 'pricedesc/'+url_id,
                data:{ st_date: clickdate},
                success: function(res){
                  $('#amt_msg').html(res.result);
                  // console.log(res);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                  alert("Error: " + XMLHttpRequest.responseJSON.error);
                } 
              });
            }
          },
        });      
      },
      error:function(XMLHttpRequest, textStatus, errorThrown) {
        alert("Error: " + XMLHttpRequest.responseJSON.errors);
      },
    });
  });
  $("#coupon_btn").button().on( "click", function(e) {
    e.preventDefault();    
    if($("#coupon").val() == '' || $("#coupon").val() == null) alert('Les coupons ne peuvent pas être vides!!!');
    else {
      var code = $("#coupon").val();
      var date = $('#start_date').val();
      if( code && url_id && date){
        $.ajax({
          type: "POST",
          url: "applycoupon",
          data: { coupon: code, cyclo: url_id, date: date },
          success: function (resp){
            // console.log(resp);
            $('#disc_val').html('Votre coupon a été enregistré. Le prix après remise est '+ resp.price_after +"€ / jour");
            $('#discounted').val(resp.price_after);
            $('#disc_in_percent').val(resp.discount_in_per);
            $('#applied').val(true);
            // alert('success');
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) { 
            alert("Error: " + XMLHttpRequest.responseJSON.errors);
            $('#disc_val').html('Le coupon fournit n’est pas valide');
            $('#discounted').val('');
            $('#disc_in_percent').val('');
            $('#applied').val(false);
          }
        });
      } else alert('Première date choisie & cyclo');
    }
  });

  $("#dateForm").validate({
    lang : 'fr',
  });
  $.extend($.validator.messages, {
    required: "Ce champs est obligatoire.",
    email: "Veuillez entrer une adresse email",
  });
  //new
  $("#pay").on('click', function( e ) {
    if(!$checkbox.is(':checked') ) {
      e.preventDefault();
      alert('S\'il vous plaît accepter les termes et conditions!');
    }
    if( $('#dateForm').valid() ){
      $("#dateForm").submit();
      $('#pay').prop('disabled', true);
    }
  });
  /*$("#pay_additional").on('click', function( e ) {
    e.preventDefault();
    $('#additional_book').val('1');
    if( $('#dateForm').valid() ){
      $("#dateForm").submit();
    }
  });*/
</script>
@stop