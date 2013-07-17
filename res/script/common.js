if (typeof console == "undefined") {
  var console = { log: function() {} };  
}
$(document).ready(function(){  
  $(".js-toggle-settings").click(function(e) { e.preventDefault(); $(".js-settings").slideToggle(100); });
  if($(".js-tub-temp").length > 0) {
    setInterval(function(){
      var url = '/ajax/current';
      jQuery.ajax({
    		type: 'get',		
    		url: url,
      	timeout: 5000,
      	dataType: 'json',
        success: function (data, textStatus, XMLHttpRequest) {
          if(data && data.tubTemp) {
            console.log(data);
            $(".js-tub-temp").html(data.tubTemp);
            $(".js-last-checked").html(data.lastChecked);
            $(".js-tub-state").html(data.tubStateOn ? "on" : "off");
            if(data.tubStateOn) {
              $(".js-tub-state").removeClass("off").addClass("on");
            } else {
              $(".js-tub-state").addClass("off").removeClass("on");
            }
            if(data.lastCheckedRecently) {
              $(".js-last-checked").removeClass("off");              
            } else {
              $(".js-last-checked").addClass("off");              
            }            
            if(data.airTemp) {
              $(".js-air-temp").html(data.airTemp);              
            }
          }
        },
        complete: function(jqXHR, textStatus) {
//            console.log(textStatus);
        },
        error: function(jqXHR, textStatus) {
        }
      });
    }
    , 120000);
  }
  if($("#js-date").length > 0) {
    $("#js-date").datepicker({minDate: new Date(), dateFormat: "yy-mm-dd"});
  }
});
function validateTubTime() {
  var dateOk = false;
  var timeOk = false;
  var tempOk = false;
  if($("#js-date").val().length > 0) {
    var date = formatDate(new Date($("#js-date").val()));
    var today = formatDate(new Date());
    if(date && date >= today) {
      dateOk = true;
    }
  }
  if($("#js-time").val().length > 0) {
    var time = $("#js-time").val().replace(":", '');    
    if(time.match(/^\d{1,2}\d{2}$/)) {
      $("#js-time").val(time);
      timeOk = true;
    }
  }
  if($("#js-temp").val().length > 0) {
    var temp = parseFloat($("#js-temp").val().replace(",", "."));    
    if(temp > 5 && temp < 50) {
      $("#js-temp").val(temp);
      tempOk = true;
    }
  }
  if(!dateOk) {
    $("#js-date").addClass("invalid");
  } else {
    $("#js-date").removeClass("invalid");
  }
  if(!timeOk) {
    $("#js-time").addClass("invalid");
  } else {
    $("#js-time").removeClass("invalid");
  }
  if(!tempOk) {
    $("#js-temp").addClass("invalid");
  } else {
    $("#js-temp").removeClass("invalid");
  }
  return dateOk && timeOk && tempOk;
}
function deleteTubTime() {
  var url = '/ajax/deletetubtime';
  jQuery.ajax({
    type: 'get',
    url: url,
    timeout: 5000,
    dataType: 'json',
    success: function (data, textStatus, XMLHttpRequest) {
    
    }
  });
}
function formatDate(date) {
  if(date != "Invalid Date" && typeof(date) == "object" && typeof(date.getFullYear) == "function") {
    return '' + date.getFullYear() + '-' + (date.getMonth() < 9 ? '0' : '') + (date.getMonth() + 1) + '-' + (date.getDate() < 10 ? '0' : '') + date.getDate();    
  }
  return false;
}
function parseDate(input, format) {
  format = format || 'yyyy-mm-dd'; // default format
  var parts = input.match(/(\d+)/g), 
      i = 0, fmt = {};
  // extract date-part indexes from the format
  format.replace(/(yyyy|dd|mm)/g, function(part) { fmt[part] = i++; });

  return new Date(parts[fmt['yyyy']], parts[fmt['mm']]-1, parts[fmt['dd']]);
}
function shortText(text, maxLength) {
  text = text.replace(/<p>/g, '').replace(/<\/p>/g, '').replace(/<i>/g, '').replace(/<\/i>/g, '');
  var words = text.split(' ');
  var shortText = '';
  for(var i = 0; i < words.length; i++) {
    if(shortText.length + words[i].length + 1 <= maxLength) {
      shortText += ' ' + words[i];
    }
  }
  if(text.length > shortText.length) {
    shortText += '...';
  }
  return shortText;
}