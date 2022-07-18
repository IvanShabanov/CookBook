
    function setLocation(curLoc){
        try {

          /* Добавить адрес в историю, */
          /* если надо дать возможность вернуться  */
          /* на прежний адрес нажав в браузере "назад" */
          window.history.pushState(null, null, curLoc);

          /* Замена текущего адреса в истории, похож на редирект */
          /* window.history.replaceState(null, null, curLoc); */

          return;
        } catch(e) {}
        window.location = curLoc;
    }
