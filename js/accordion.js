function toggleTab(element){
    var parent = element.parentNode;
    
    var content = parent.querySelector(".tabcontent");

    content.classList.toggle("hide");
    parent.querySelector("span.line").classList.toggle("hide");
    
}