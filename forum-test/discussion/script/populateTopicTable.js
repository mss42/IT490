let topicrequest = new XMLHttpRequest();
$(document).ready( () => {
  //Fungerar likt de andra JS-skripten. Skickar iväg en XMLHttpRequest till get_topics.php för att hämta ut alla trådar i en viss kategori. Svaret används sedan av response-metoden
  //tack vare eventlistenern. Metoden bygger upp en tabell och gör exempelvis trådens titel till en länk till den aktuella tråden.
  let categoryTable = document.getElementById("categories");
  topicrequest.addEventListener("load", response);
  function response() {
    if(JSON.parse(this.responseText).length === 0){
      let emptyInfo = document.createElement("h1");
      emptyInfo.innerHTML = "Här var det tyvärr tomt!";
      let container = document.getElementsByClassName("main")[0];
      emptyInfo.style.textAlign = 'center';
      container.appendChild(emptyInfo);
    } else {
      let response = JSON.parse(this.responseText);
      let options = document.getElementById("options");
      let navtext = "<a href=\"index.php\">Hem</a> > <a href=\"forums.php?category=" + response[0]['category'] +"\">" + response[0]['catname'] + "</a>";
      options.innerHTML += navtext;
      for(let i = 0; i < response.length; i++){
        let newRow = document.createElement("tr");
        let titleCell = document.createElement("td");
        let authorCell = document.createElement("td");
        let dateCell = document.createElement("td");
        let replycountCell = document.createElement("td");
        titleCell.innerHTML = '<a href="forums.php?category=' + response[i]['category'] + '&topic='+ response[i]['id'] + '">' + response[i]['title'] + '</a>';
        authorCell.innerHTML = response[i]['author'];
        dateCell.innerHTML = response[i]['created'];
        replycountCell.innerHTML = response[i]['count'];
        newRow.appendChild(titleCell);
        newRow.appendChild(authorCell);
        newRow.appendChild(dateCell);
        newRow.appendChild(replycountCell);
        categoryTable.appendChild(newRow);
      }
    }
  }
  topicrequest.open('GET', 'script/get_topics.php');
  topicrequest.send();
});
