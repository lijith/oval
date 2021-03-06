(function($) {
    "use strict";
    $(document).ready(function() {
        /*==Left Navigation Accordion ==*/
        if ($.fn.dcAccordion) {
            $('#nav-accordion').dcAccordion({
                eventType: 'click',
                autoClose: true,
                saveState: true,
                disableLink: true,
                speed: 'slow',
                showCount: false,
                autoExpand: true,
                classExpand: 'dcjq-current-parent'
            });
        }
        /*==Slim Scroll ==*/
        if ($.fn.slimScroll) {
            $('.event-list').slimscroll({
                height: '305px',
                wheelStep: 20
            });
            $('.conversation-list').slimscroll({
                height: '360px',
                wheelStep: 35
            });
            $('.to-do-list').slimscroll({
                height: '300px',
                wheelStep: 35
            });
        }
        /*==Nice Scroll ==*/
        if ($.fn.niceScroll) {


            $(".leftside-navigation").niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px"
            });

            $(".leftside-navigation").getNiceScroll().resize();
            if ($('#sidebar').hasClass('hide-left-bar')) {
                $(".leftside-navigation").getNiceScroll().hide();
            }
            $(".leftside-navigation").getNiceScroll().show();

            $(".right-stat-bar").niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px"
            });

        }


        /*==Collapsible==*/
        $('.widget-head').click(function(e) {
            var widgetElem = $(this).children('.widget-collapse').children('i');

            $(this)
                .next('.widget-container')
                .slideToggle('slow');
            if ($(widgetElem).hasClass('ico-minus')) {
                $(widgetElem).removeClass('ico-minus');
                $(widgetElem).addClass('ico-plus');
            } else {
                $(widgetElem).removeClass('ico-plus');
                $(widgetElem).addClass('ico-minus');
            }
            e.preventDefault();
        });


        /*
        * check all uncheck all
         */
         $('.batch-coupon-checkall').click(function(e) {
            $('.check-coupon').prop('checked', true);
            e.preventDefault();
         })
         $('.batch-coupon-uncheckall').click(function(e) {
            $('.check-coupon').prop('checked', false);
            e.preventDefault();
         })

         $('#pack-cancel-form .btn').click(function(e){
            e.preventDefault();
            bootbox.confirm("Are you sure you want to cancel this pack?", function(result) {
              if(result === true){
                console.log(result);
                $('#pack-cancel-form').submit();
              }
            }); 
         })

         



        /*==Sidebar Toggle==*/

        $(".leftside-navigation .sub-menu > a").click(function() {
            var o = ($(this).offset());
            var diff = 80 - o.top;
            if (diff > 0)
                $(".leftside-navigation").scrollTo("-=" + Math.abs(diff), 500);
            else
                $(".leftside-navigation").scrollTo("+=" + Math.abs(diff), 500);
        });



        $('.sidebar-toggle-box .fa-bars').click(function(e) {

            $(".leftside-navigation").niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px"
            });

            $('#sidebar').toggleClass('hide-left-bar');
            if ($('#sidebar').hasClass('hide-left-bar')) {
                $(".leftside-navigation").getNiceScroll().hide();
            }
            $(".leftside-navigation").getNiceScroll().show();
            $('#main-content').toggleClass('merge-left');
            e.stopPropagation();
            if ($('#container').hasClass('open-right-panel')) {
                $('#container').removeClass('open-right-panel')
            }
            if ($('.right-sidebar').hasClass('open-right-bar')) {
                $('.right-sidebar').removeClass('open-right-bar')
            }

            if ($('.header').hasClass('merge-header')) {
                $('.header').removeClass('merge-header')
            }


        });
        // $('.toggle-right-box .fa-bars').click(function(e) {
        //     $('#container').toggleClass('open-right-panel');
        //     $('.right-sidebar').toggleClass('open-right-bar');
        //     $('.header').toggleClass('merge-header');

        //     e.stopPropagation();
        // });

        $('.header,#main-content,#sidebar').click(function() {
            if ($('#container').hasClass('open-right-panel')) {
                $('#container').removeClass('open-right-panel')
            }
            if ($('.right-sidebar').hasClass('open-right-bar')) {
                $('.right-sidebar').removeClass('open-right-bar')
            }

            if ($('.header').hasClass('merge-header')) {
                $('.header').removeClass('merge-header')
            }


        });


        $('.panel .tools .fa').click(function() {
            var el = $(this).parents(".panel").children(".panel-body");
            if ($(this).hasClass("fa-chevron-down")) {
                $(this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
                el.slideUp(200);
            } else {
                $(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
                el.slideDown(200);
            }
        });



        $('.panel .tools .fa-times').click(function() {
            $(this).parents(".panel").parent().remove();
        });

        // tool tips

        $('.tooltips').tooltip();

        // popovers

        $('.popovers').popover();


    });

    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });

    if ($('.print-coupon').length > 0) {
        $('button.print').on('click', function() {
            $(".print-coupon").print();
        })
    }

    if ($('.print-username-stat').length > 0) {
        $('button.print').on('click', function() {
            $(".print-username-stat").print();
        })
    }

    if ($('.print-username-details').length > 0) {
        $('button.print').on('click', function() {
            $(".print-username-details").print();
        })
    }

    if ($('.print-patient-usage').length > 0) {
        $('button.print').on('click', function() {
            $(".print-patient-usage").print();
        })
    }

    if ($('.print-plan-usage').length > 0) {
        $('button.print').on('click', function() {
            $(".print-plan-usage").print();
        })
    }

    $('.default-date-picker').datepicker({
        format: 'mm-dd-yyyy'
    });

    // disabling dates
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var checkin = $('.dpd1').datepicker({
        onRender: function(date) {
            return date.valueOf() < now.valueOf() ? 'disabled' : '';
        }
    }).on('changeDate', function(ev) {
        if (ev.date.valueOf() > checkout.date.valueOf()) {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setValue(newDate);
        }
        checkin.hide();
        $('.dpd2')[0].focus();
    }).data('datepicker');
    var checkout = $('.dpd2').datepicker({
        onRender: function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
        }
    }).on('changeDate', function(ev) {
        checkout.hide();
    }).data('datepicker');


    //mobile number find ajax form
    var options = {
        beforeSubmit: showRequest, // pre-submit callback 
        success: showResponse // post-submit callback 

    };

    //handle form submit
    $('#find-mobilenumber').submit(function() {

        $(this).ajaxSubmit(options);

        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false;
    });

    function showRequest(formData, jqForm, options) {
        // formData is an array; here we use $.param to convert it to a string to display it 
        // but the form plugin does this for you automatically when it submits the data 
        var queryString = $.param(formData);

        // here we could return false to prevent the form from being submitted; 
        // returning anything other than false will allow the form submit to continue 
        return true;
    }

    // post-submit callback 
    function showResponse(responseText, statusText, xhr, $form) {
        var response =  JSON.parse(responseText);
        var insertHTML = '<div class="alert alert-warning fade in"><button type="button" class="close close-sm" data-dismiss="alert"><i class="fa fa-times"></i></button><strong>'+response.mobile_number+'</strong></div>'
       $("#output").html(insertHTML);

    }


})(jQuery);