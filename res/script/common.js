var gridCollumns = 1;
var gridRowsPerPage = 5;
var imagePage = 1;

$(document).ready(function(){  
  $('html').removeClass('no-js').addClass('js');
  // Modernizr incorrectly sets some features to active when they are not. This is a quick fix
  correctModernizrClasses();
  // Browser specific warnings
  checkBrowser();
  // Set grid based on screen width
  setGrid();
  // Set link events 
  // Update results on window resize and scroll
//  setWindowEvents();
  getImages();
});

function setGrid() {
  var collumns = getGridCollumns();
  var gridIsChanged = false;
  if(!isNaN(collumns) && collumns != gridCollumns) {
    gridCollumns = collumns;
    for(var i = 3; i < 20; i++) {
      $('body.destination').removeClass('grid-' + i);
    }
    $('body.destination').addClass('grid-' + gridCollumns);
    gridIsChanged = true;
  }
  return gridIsChanged;
}
function getGridCollumns() {
  var collumns = parseInt(($(window).width() - 410) / 170);
  if( collumns < 3 ) {
    collumns = 3;
  }
  if( collumns > 20 ) {
    collumns = 20;
  }
  return collumns;  
}

/* AJAX REQUESTS */
var searchIsPerformed = false;
// Do destination search
function getImages() {  
  if(!searchIsPerformed) {
    searchIsPerformed = true;
    var url = '/ajax/images?collumns=' + gridCollumns;
    console.log(url);
    jQuery.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      timeout: 10000,
      success: function (data, textStatus, XMLHttpRequest) {
  //      console.log(data);
        if(textStatus == 'success'){
          showImages(data, false);
        }
      }
    });
  }
}
function showImages(data, appendResult) {
  if(!appendResult) {
    images = new Array();
  }
  var collumns = new Array();
  resultHTML = '';
  if('errorMessage' in data) {
    writeErrorMessage('We encountered an error when searching for images. Error message: ' + data.errorMessage);
  }
  else {
    if(data.length == 0) {
      imagesPage = -1;
      if(!appendResult) {
        resultHTML = '<li class="collumn"><ul><li class="no-result"><h4>No results found</h4><div class="text"><p>There are no images right here.</p></div></li></ul></li>';
      }
    }
    else {
      var result = data;
      var index = 0;
//      console.log(result);
      for(var collumn in result) {
        var collumnHTML = '';
        for(var j = 0;j < result[collumn].length; j++) {
//          console.log(result[collumn][j]);
          var m = getImageMarkup(result[collumn][j], index);
          collumnHTML += m;
          index++;
        }
        collumns.push(collumnHTML);
        resultHTML += '<li class="collumn"><ul>' + collumnHTML + '</ul></li>';
      }
    }
    if(appendResult) {
      for(var i = 0; i < collumns.length; i++) {
        $('#boxes ul li:nth-child(' + (i + 1) + ') ul').append(collumns[i]);
      }
    } else {
      $('#images ul').html(resultHTML);
    }
  }
  searchIsPerformed = false;
  $('#boxes').removeClass('pending');
}

function getImageMarkup(data, index) {
  markup = '<li><a href="' + data.url + '" target="_blank" onclick="return openImage(\'' + data.url + '\');"><img src="' + data.urlThumb + '" alt="" /></a></li>';
  return markup;
}

function setBlackout(show) {
  if(show) {
    $('body').addClass('show-blackout');
  } else {
    $('body').removeClass('show-blackout');
  }
}

/* INTERFACE FUNCTIONS */
// Handle tabbing through destination search results
function tabLocationResults(e) {
  if(eventIsArrowUp(e) || eventIsArrowDown(e)) {
    var results = $('#location-results li a');
    if(results.length > 0) {
      var currentResult = $('#location-results a:focus');
      if(eventIsArrowUp(e)) {
        if(currentResult) {
          var currentLi = currentResult.parent();
          var prevLi = currentLi.prev();
          if(prevLi.length > 0) {
            prevLi.children()[0].focus();
          }
          else {
            $('#location').focus();  
          }
        }
        else {
          $('#location-results li:first-child a').blur();
        }
      }
      else {
        if(currentResult.length > 0) {
          var currentLi = currentResult.parent();
          var nextLi = currentLi.next();
          if(nextLi.length > 0) {
            nextLi.children()[0].focus();
          }
        }
        else {
          $('#location-results li:first-child a').focus();
        }
      }
      e.preventDefault();
      return false;
    }
  }
  return false;
}
function performSearchForEvent(e) {
//  console.log(e.keyCode);
  if((65<=e.keyCode && e.keyCode<=90) //letter
    || (e.keyCode == 8) // backspace 
    || (e.keyCode == 46) // delete 
  ) {
    return true;
  }
  return false;
}
function eventIsEnter(e) {
  return e.keyCode == 13;
}
function eventIsArrowUp(e) {
  return e.keyCode == 38;  
}
function eventIsArrowDown(e) {
  return e.keyCode == 40;  
}
function eventIsTab(e) {
  return e.keyCode == 9;  
}
// Handle scrolling and window resizing
function setWindowEvents() {
  if($('body').hasClass('destination')){
    $(window).resize(function() {
      if(setGrid(false)) {
        updateTimespanSlider();
      }
      setBoxesHeight();    
      // Set slimscroll on all detail views (will only do so if they are high enough)
      setDetailSlimScroll('detail');
      setDetailSlimScroll('about');
      setDetailSlimScroll('share');
    });
    $(window).scroll(function(e){
      if($(window).scrollTop() > 168) {
        if(!$('#boxes').hasClass('sticky-menu')) {
          $('#boxes').addClass('sticky-menu');
        }
      } else {
        if($('#boxes').hasClass('sticky-menu')) {
          $('#boxes').removeClass('sticky-menu');
        }
      }
      var scrollBottom = $('html').height() - $(window).height() - $(window).scrollTop();
      if(scrollBottom < 300) {      
        var val1 = $("#timespan-slider").slider('values', 0);
        var val2 = $("#timespan-slider").slider('values', 1);
        var today = new Date();
        var fromTimestamp = new Date().setDate(today.getDate() + val1);;
        var untilTimestamp = new Date().setDate(today.getDate() + val2);;
        var from = new Date(fromTimestamp);
        var until = new Date(untilTimestamp);
        getRecommendations(from, until);
      }
    });
  }
}
function categoryDisplayName(categorySlug) {
  if(categorySlug == 'entertainment') {
    return 'Entertainment';
  }
  else if(categorySlug == 'foodandbars') {
    return 'Food & Bars';
  }
  else if(categorySlug == 'accommodation') {
    return 'Accommodation';
  }
  else if(categorySlug == 'shopping') {
    return 'Shopping';
  }
}
function categoryId(categorySlug) {
  if(categorySlug == 'entertainment') {
    return 1;
  }
  else if(categorySlug == 'foodandbars') {
    return 2;
  }
  else if(categorySlug == 'accommodation') {
    return 3;
  }
  else if(categorySlug == 'shopping') {
    return 4;
  }
  return '';
}
function categorySlug(categoryId) {
  if(categoryId == 1) {
    return 'entertainment';
  }
  else if(categoryId == 2) {
    return 'foodandbars';
  }
  else if(categoryId == 3) {
    return 'accommodation';
  }
  else if(categoryId == 4) {
    return 'shopping';
  }
  return '';
}
function displayMonth(monthIndex) {
  if(monthIndex == 0) {
    return 'jan';
  }
  else if(monthIndex == 1) {
    return 'feb';
  }
  else if(monthIndex == 2) {
    return 'mar';
  }
  else if(monthIndex == 3) {
    return 'apr';
  }
  else if(monthIndex == 4) {
    return 'may';
  }
  else if(monthIndex == 5) {
    return 'jun';
  }
  else if(monthIndex == 6) {
    return 'jul';
  }
  else if(monthIndex == 7) {
    return 'aug';
  }
  else if(monthIndex == 8) {
    return 'sep';
  }
  else if(monthIndex == 9) {
    return 'oct';
  }
  else if(monthIndex == 10) {
    return 'nov';
  }
  else if(monthIndex == 11) {
    return 'dec';
  }
  return '';
}
function checkifEmpty(input){
  if(input.value == input.getAttribute("placeholder")){
    return true;  
  }
  return input.value.replace(/ /g, '').length == 0;
}
function validateEmail(value){
  var atpos=value.indexOf("@");
  var dotpos=value.lastIndexOf(".");
  if (atpos<1 || dotpos<atpos+2 || dotpos+2>=value.length){
    return false;
  }
  return true;
}
function writeErrorMessage(message){
  var errorMessagePanel = document.getElementById('pnl_errormessage');
  if(errorMessagePanel != undefined){
    if(message.length > 0){
      errorMessagePanel.style.display = 'block';
    }else{
      errorMessagePanel.style.display = 'none';  
    }
    setTimeout('printErrorMessage("' + message + '");', 500);
  }else if(message.length > 0){
    message = message.replace('<br/>', ' ');
    alert(message);  
  }
}
function printErrorMessage(message) {
  var errorMessagePanel = document.getElementById('pnl_errormessage');
  errorMessagePanel.innerHTML = '<div class="hidden">' + errorMessagePanel.innerHTML + '</div>' +  message;
}

/* SYSTEM FUNCTIONS */

function checkBrowser(){
  // Writes error if ie6
  var ie6 = isIe6();
  if(ie6){
    writeErrorMessage('This site is not completely compatible with Internet Explorer 6. Upgrade your browser for best result, or try another browser — e.g. Firefox or Chrome.');
  }
}
function isIe6(){
  return $('html').hasClass('lt-ie7');
}
function isIe7(){
  return $('html').hasClass('lt-ie8');
}
function isTouchDevice() {
  return !!('ontouchstart' in window);
}
function isDesktopSafari(){
  var browser = $.browser;
  if(screen.width > 999){
    if(browser.safari){
      return true;
    }
  }
  return false;
}
// TODO: Rewrite this with jQuery
function ensurePlaceholders(){
  var checkDesktopSafari = isDesktopSafari(); // NOTE: This check is only needed if placeholders are to be centered
  if(!Modernizr.input.placeholder || checkDesktopSafari){
    var inputs = document.getElementsByTagName('input');
    for(var i = 0;i < inputs.length;i++){
      if(inputs[i].type == 'text'){
        var placeholder = inputs[i].getAttribute("placeholder");
        if(placeholder != undefined){
          if(placeholder.length > 0){
            var ie7 = isIe7();
            if(!ie7){
              setFakePlaceholder(inputs[i].id, placeholder);
              var onfocus = inputs[i].getAttribute("onfocus");
              inputs[i].setAttribute('onfocus', 'clearFakePlaceholder("' + inputs[i].id + '", "' + placeholder + '");' + (onfocus ? ';' + onfocus : ''));
              var onblur = inputs[i].getAttribute("onblur");
              inputs[i].setAttribute('onblur', 'setFakePlaceholder("' + inputs[i].id + '", "' + placeholder + '");' + (onblur > 0 ? ';' + onblur : ''));
            }
          }
        }
      }
    }    
  }
}
function clearFakePlaceholder(inputId, placeholder){
  var input = document.getElementById(inputId);
  if(input.value == placeholder){
    input.value = '';  
  }
}
function setFakePlaceholder(inputId, placeholder){
  var input = document.getElementById(inputId);
  if(input.value == '' || input.value == undefined){
    input.value = placeholder;
  }
}
function correctModernizrClasses(){
  var falselyActiveFeatures = new Array();
  var newClasses = new Array();
  var i = 0;
  var j = 0;
  if (navigator.userAgent.indexOf('iPhone OS') > -1) {
    newClasses[j] = 'ios';
    j++;
    if (navigator.userAgent.indexOf('iPhone OS 4') > -1 || navigator.userAgent.indexOf('iPhone OS 3') > -1) {
      falselyActiveFeatures[i] = 'cssgradients';
      i++;
    }
  }
  if(falselyActiveFeatures.length > 0 || newClasses.length > 0){
    var html = document.getElementsByTagName('html');
    if(html.length > 0){
      for(var i = 0;i < falselyActiveFeatures.length;i++){
        html[0].className = html[0].className.replace(' ' + falselyActiveFeatures[i], ' no-' + falselyActiveFeatures[i]);
      }
      for(var i = 0;i < newClasses.length;i++){
        html[0].className = html[0].className + ' ' + newClasses[i];
      }
    }
  }
}

function formatDate(date) {
  return '' + date.getFullYear() + (date.getMonth() < 9 ? '0' : '') + (date.getMonth() + 1) + (date.getDate() < 10 ? '0' : '') + date.getDate();
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
function copyShareUrl() {
  copyToClipboard($('#share-url').val());
}
function copyToClipboard(text)
{
    if (window.clipboardData) // Internet Explorer
    {  
        window.clipboardData.setData("Text", text);
    }
    else
    {  
    window.prompt ("Copy the URL from this text field, then click OK.", text);
    }
}
function removeLineBreaks(string) {
  string = string.replace(/(\\r\\n|\\n|\\r)/gm,"");
  return string;
}
function trackEvent(url) {
  _gaq.push(['_trackPageview', url]);
}