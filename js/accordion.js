async function openRarity(evt, rarity) {
    // Declare all variables
    var i, tabcontent, tablinks;
  
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].classList.add("hide"); 
      tabcontent[i].textContent = '';
    }
  
    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }

    try{
        var response = await fetch('https://riskof.gerios.fr/api/items.php?classification=rarity&rarityLevel='+rarity);
        var items = await response.json();
        
        if(items.length > 0){
            items.forEach(item => {
                createCard(containers[parseInt(rarity)-1], item);
            });
        }
    } catch (error) {
        console.error(error);
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById("rarity-"+rarity).classList.remove("hide");
    console.log(evt);
    
    evt.target.classList.add("active");
  }


function toggleTab(element){
    var parent = element.parentNode;
    
    var content = parent.querySelector(".tabcontent");

    content.classList.toggle("hide");
    parent.querySelector("span.line").classList.toggle("hide");
    
}