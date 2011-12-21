<script LANGUAGE="JavaScript">
 // function parameters are: field - the string field, count - the field for remaining
// characters  number and max - the maximum number of characters
 function CountLeft(field, count, max) {
 // if the length of the string in the input field is greater than the max value, trim it
 if (field.value.length > max)
 field.value = field.value.substring(0, max);
 else
 // calculate the remaining characters
 count.value = max - field.value.length;
 }
</script>