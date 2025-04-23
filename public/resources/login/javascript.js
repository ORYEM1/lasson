$('.message a').click(function(){
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});
$(document).on('click','#toggle_password',function(){
   var x = document.getElementById("password");
   if (x.type === "password") {
      x.type = "text";
   } else {
      x.type = "password";
   }
})