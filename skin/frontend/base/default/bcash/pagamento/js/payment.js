(function () {
  var cards = [ '1', '2', '37', '45', '55', '56', '63'];
      
       function clickFlag(e) {
          alert('clickFlag');
          cardConteiner = document.getElementById('card-conteiner');
          if (isCard(this) && hasClass(cardConteiner, 'b-hide')) {
            cardConteiner.className = cardConteiner.className.replace(/\bb-hide\b/,'');
          } else if (!isCard(this) && !hasClass(cardConteiner, 'b-hide')) {
            cardConteiner.className = cardConteiner.className + " b-hide";
          }
      }

      function isCard(element) {
        return cards.indexOf(element.value) > -1;
      }

      function hasClass(element, cls) {
        return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
      }
      
      function initBcashPagamento() {
        var paymentsOptions = document.getElementsByClassName("bandeira");
        for (var i = paymentsOptions.length - 1; i >= 0; i--) {
          var paymentFlags = payments[i];
          paymentFlags.onclick = clickFlag;
        };
      }

    initBcashPagamento();
});