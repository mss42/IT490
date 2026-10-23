let contentRequest = new XMLHttpRequest();
// Skript för att hämta innehållet i en tråd med hjälp av XMLHttpRequest som frågar PHP-skriptet 'get_topic_contents.php' om data.
// Requesten får en eventlistener som anropar metoden "response" vid svar. När svaret kommer (i JSON-format), parsas denna data till ett JavaScript-objekt
// Varje rad i svaret används sedan för att bygga upp strukturen i tråden genom exempelvis en div per svar i tråden
$( document ).ready( () => {
  contentRequest.addEventListener("load", response);
  let main = document.getElementsByClassName("topic_container")[0];
  let header = document.createElement("h1");
  let date_author = document.createElement("p");
  let topiccontents = document.createElement("div");
  let text = document.createElement("p");
  let divider = document.createElement("hr");
  topiccontents.setAttribute("class", "topic_contents");
  topiccontents.appendChild(header);
  topiccontents.appendChild(date_author);
  topiccontents.appendChild(divider);
  topiccontents.appendChild(text);
  main.appendChild(topiccontents);
  function response() {
    let response = JSON.parse(this.responseText);
    header.innerHTML = response[0]["title"];
    date_author.innerHTML = "<strong>" + response[0]["author"] + "</strong> " + response[0]["created"];
    text.innerHTML = response[0]["text"];
    for(let i = 0; i < response[0]['replies'].length; i++){
      let postdiv = document.createElement("div");
      postdiv.setAttribute("class", "postdiv");
      let posttext = document.createElement("p");
      let authortext = document.createElement("p");
      let divider = document.createElement("hr");
      authortext.innerHTML = "<strong>"+response[0]['replies'][i]['author']+"</strong> "+response[0]['replies'][i]['created'];
      posttext.innerHTML = response[0]['replies'][i]['content'];
      postdiv.appendChild(authortext);
      postdiv.appendChild(divider);
      postdiv.appendChild(posttext);
      main.appendChild(postdiv);
    }
  }
  contentRequest.open('GET', 'script/get_topic_contents.php');
  contentRequest.send();
})
