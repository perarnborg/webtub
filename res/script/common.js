$(document).ready(function(){  
});
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