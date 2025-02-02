document.addEventListener('DOMContentLoaded', function(){

  

  let addDeckBtn = document.getElementById('add_Deck__Btn');
  addDeckBtn.addEventListener('click', function(){
    // opens a this.ariaModal
    let modal = makeModal();
    modal.innerHTML = `
      <div class = "login__Cont">
        <div class="login_header">
          <h6>Please fill out Deck information:</h6>
        </div>
        <form class="login__form" id = "add-Deck__Form" method = "post">

          <label class="label" for="deckName__input">Deckname: </label>
          <input class="deckName__input" required type="text" name="deckName__input" id="deckName__input" placeholder="Name of new deck ...">
          <div class="invalid-input__err">
            <?php echo $deckNameErr; ?>
          </div>

          <div class="category__Sec">
                 
            <div>
              <label class="label" for="category__input">Category: </label>
              <select name="category__input" id="category__input" required >
                <optgroup label="Language">
                  <optgroup label="words">
                    <option value="deutsch">Deutsch</option>
                  </optgroup>
                  <optgroup label="expressions">
                    <option value="deutsch">deutsch</option>
                  </optgroup>
                </optgroup>
                <optgroup label="General">
                  <option value="math">Math</option>
                </optgroup>
              </select>
              <div class="invalid-input__err">
                <?php echo $categoryErr; ?>
              </div>
            </div>

            <div>
              <button type="button" class="icon__btn" id="add_Category__Btn">
                <span class="material-symbols-outlined">
                  add
                </span>
              </button>
            </div>

          </div>

          <label class="label" for="description__input">Description</label>
          <textarea name="description__input" id="description__input" cols="30" rows="10" placeholder="Enter a brief explaination"></textarea>  
          <div class="invalid-input__err">
            <?php echo $descriptionErr; ?>
          </div> 

          <div class="formBtns__div">
            <button class= "btn" type="submit" id= "submitDeckBtn">Register</button>
            <button class= "btn" type="reset">Cancel</button>
          </div>
        </form>
      </div>
    `
    let form = document.getElementById('add-Deck__Form');
    form.addEventListener('submit', function(e){
      e.preventDefault();
      let deckData = new FormData(this);
      let action =  'addDeck';
      // show(post(deckData, action)); 
      fetch( 'http://localhost:3000/api/post.php?action=' + action,
      { method : 'POST',
      // headers: { 
      //   'Content-Type' : 'application/json'
      // },
      // body : JSON.stringify(deckData)
      body: deckData
      })
      .then(res => {return res.json();
      })
      .then(async data => {
        switch (data) {
          case 'The deck was Successfully added.':
            modal.parentElement.remove();
            await showDecks();
            let deckName = deckData.get('deckName__input');
            selectDeck(deckName);

            
            
            showDeckDetails(data[0]);
            console.log(addedDeckLiEl)
            break;
        
          default:
            console.log(data)
            break;
          }
        }
      )
      .catch( error => console.error('Error:', error)) 
    })   
  });

  //modal
  function makeModal(){
    var modal = document.createElement('div');
    modal.setAttribute('id' , 'modal');

    modal.style.zIndex = '2';
    modal.style.width = "100%";
    modal.style.height = "100%";
    modal.style.position = "fixed";
    modal.style.backgroundColor = "rgba(0,0,0,0.9)";
    modal.style.top = "0";
    modal.style.left = "0";
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';

    var close = document.createElement('span');
    close.style.position = 'absolute';
    close.style.top = '15px'
    close.style.right = '35px'
    close.style.color = "#f1f1f1"
    close.style.fontSize = '2rem'
    close.style.transition = '0.3s'
    close.innerHTML = `
    &times;`
    close.addEventListener('click',()=>{
      if(confirm('do you want to save the changes?')){
        console.log('cansel confirmed');
        // window.localStorage.setItem("projects", JSON.stringify(projects));
        modal.remove()   
      }
    })

    var content = document.createElement('div');
    content.setAttribute('id' , 'modalContent');
    content.style.position = 'relative';
    content.style.padding = '2rem ';
    content.style.backgroundColor = 'white'
    content.style.maxWidth = "80%";
    content.style.margin = '40px Auto'



    modal.append(close);
    modal.append(content);
    document.body.append(modal);
    return content;
  }
  //save to db

  //show on page

  // const post = (formdata, action) => {
  //   // fetch( 'http://localhost:3000/api/post.php#' + action,
  //   fetch( 'http://localhost:3000/ajax.php',
  //       { method : 'POST',
  //       // headers: { 
  //       //   'Content-Type' : 'application/json'
  //       // },
  //       // body : JSON.stringify(deckData)
  //       body: formdata
  //       })
  //       .then(res => {return res.json();
  //       })
  //       .then(data => {return data}
  //       )
  //       .catch( error => console.error('Error:', error))         
  // }

  // const show = (data) => {
  //   console.log(data);
  // }

  const  showDecks = async() =>
  {
    const container = document.getElementById('decksNameList');
    var decksArr = await getDecksArr();
    var deckList = document.createElement('ul');
    //empty container
    container.innerHTML = '';

    if (decksArr)
    {

      for (let i = 0; i < decksArr.length; i++) {
        const liEl = document.createElement('li');
        liEl.classList.add('subContainer', 'tree');

        liEl.innerHTML = `
          
          <p class = "deckName">
          ${decksArr[i]}
          </p>
          
        `;

        liEl.addEventListener('click', (e) => {
          e.stopPropagation();
          
          selectLi(e.target);
  
  
  
        });
  
  
        deckList.append(liEl);
  
      
      }
      container.append(deckList);

    }    
  }

  const selectLi = async (targetEl)=> 
  {

    const selectLi = targetEl.closest('li');

    // const subUl = selectLi.lastElementChild;


    // if (targetEl.localName === "span") {

    //   selectLi.classList.toggle("openedLi");
    //   subUl.classList.toggle("openedUL");

    // };

    const parentUl = selectLi.closest('ul');
    // const projectId = selectLi.getAttribute("project_id");

    // if (parentDiv.classList.contains('projectTree') || parentDiv.classList.contains('favProjects')) {
    //   const project = findProject(projects, projectId);
    //   showProject(project);
    // }

    
    // const activeMenu = activeMenuEl[1].querySelectorAll('.active');

    // if (activeMenu.length > 0) { activeMenu[0].classList.remove('active'); };

    // activeMenuEl[1].firstElementChild.classList.add('active');

    const selectedEls = parentUl.querySelectorAll("li.selected");
    if (selectedEls.length > 0) 
    {
      for (let i=0; i <selectedEls.length; i++)
      {
        selectedEls[i].classList.remove('selected'); 

      }
    };

    selectLi.classList.add('selected');
    //get deck name
    const deckName = selectLi.querySelector('.deckName').innerText;
    //get deck info from DB
    const deckInfo = await getDeckDetails(deckName);
    //show deck info 
    showDeckDetails(deckInfo[0]);
    
  };

  const getDeckDetails = async (dName) => {
    let Method = 'GET';
    let header = { 
      'Content-Type' : 'application/json'
    };
    let value = dName;
    let action = 'DeckData';

    const deckInfo = await fetch( 'http://localhost:3000/api/index.php?' + "deckName=" + value + "&action="+action,
      { method : Method,
      headers: header
      // body : JSON.stringify(deckData)
      })
      .then(res => {return res.json();
      })
      .then(data => {
        // switch (data) {
        //   case 'The deck was Successfully added.':
        //     refreshDeckList();
        //     break;
        
        //   default:
        //     break;
        // }
        // console.log(data);
        return data}
      )
      .catch( error => console.error('Error:', error)) 
      return deckInfo;
  } 
  

  const showDeckDetails = (info) => {
      const viewContainer = document.getElementById('mainCenterDisplay');
      viewContainer.innerHTML = `
      <div class='deck-Info' id="deckInfo">
      
        <div class="deck-Info_Header">
          <div class="deck-Info_Header_Left">
            <p>Category : ${info.deckCategory}</p>
            <p>Created at ${info.timeCreated}.Last edited at ${info.timeEdited}</p>
          </div>
          <div class="deck-Info_Header_Right">
            <span class="material-symbols-outlined">
              share
            </span>
          </div>
        </div>

        <div class="deck-Info_Summary">
          <div class="deck-Info_Summary_Name">
            <h4>${info.deckName}</h4> 
            <span>${info.description}</span>
          </div>
          <div class="deck-Info_Summary_Description">
            <h4>${info.deckDescription}</h4>                    
          </div>    
        </div>

        <div class="deck-Info_Content">
          <div class="deck-Info_Content_Number">
          ${info.totalCards}
          </div>
          <div class="deck-Info_Content_Masetery">
          ${info.mastery}
          </div>
          <div class="deck-Info_Content_Record">
          ${info.record}
          </div>
        </div>

        <div class="deck-Info_Footer">
          <div class="deck-Info_Footer_Actions" id ="deck-Info_Footer_Actions__div">
            <button type="button" class="GoBtn addCardBtn" id= "addCardBtn_deck-Info_Footer_Actions" >Add card</button>
            <button type="button" class="GoBtn learnBtn" id= "learnBtn_deck-Info_Footer_Actions">Learn</button>
            <button type="button" class="GoBtn testBtn" id= "testBtn_deck-Info_Footer_Actions">Test</button>
            <button type="button" class="cancelBtn deleteBtn" id= "deleteBtn_deck-Info_Footer_Actions">Delete</button>
            <button type="button" class="GoBtn settingBtn" id= "settingBtn_deck-Info_Footer_Actions">Setting</button>
          </div>

        </div>
      </div> 
     
      `
      buttons = document.getElementById("deck-Info_Footer_Actions__div").children;
      addCardBtn = buttons[0];
      learnBtn = buttons[1];
      testBtn = buttons[2];
      deleteBtn = buttons[3];
      settingBtn = buttons[4];

      addCardBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        loadEmptyCard(info.deckName);

      })

      learnBtn.addEventListener('click', async(e)=>{
        e.preventDefault();
        let cards = await getAllCards(info.deckName);
        loadDeck(cards);
        // console.log(cards)

      })

      testBtn.addEventListener('click',async (e)=>{
        e.preventDefault();
        let cards = await getAllCards(info.deckName);
        loadDeck(cards);

      })


  }

  const test = (e)=>{
    // e.preventDefault();
    console.log(e);
  }

  const loadEmptyCard = (deckName)=>{
    const viewContainer = document.getElementById('mainCenterDisplay');
      viewContainer.innerHTML =`
        <form method="post" id="emptyCardForm" class="emptyCardForm">

          <div class="emptyCardHeader">
            <button type="button" class= "Btn cardSaveBtn" id = "cardsSaveBtn"> Done </button>
            <span class="emptyCardHeader_DeckName">${deckName}</span>
            <button type="submit" class= "Btn addNewCardBtn" id= "addNewCardBtn"> + </button>
          </div>

          <div class="emptyCardFront">
            <textarea name="cardFrontText" id="emptyCardFrontText" cols="30" rows="10" placeholder="Front of the card"></textarea>
          </div>

          <div class="emptyCardBack">
          <textarea name="cardBackText" id="emptyCardBackText" cols="30" rows="10" placeholder="Back of the card"></textarea>
          </div>
          
        </form>
      `
      const newCardBtn =  document.getElementById('addNewCardBtn');
      const addCardDoneBtn =  document.getElementById('cardsSaveBtn');
      

      newCardBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        const cardData = new FormData(document.getElementById('emptyCardForm'));


        addCardToDeck(deckName, cardData);
        loadEmptyCard(deckName);
      })

      addCardDoneBtn.addEventListener ( 'click', (e)=>{
        e.preventDefault();
        addCardToDeck(deckName, cardData);
        selectDeck(deckName);
      })
  }

  const selectDeck= (deckName)=>{
    const addedDeckLiEl = document.querySelector('div.main-Left_Cards ul').children;
    for (const liEl of addedDeckLiEl) {
      

      if (liEl.lastElementChild.innerText == deckName) {
        selectLi(liEl);
      }  
    }
  }

  const addCardToDeck = async(deckName, cardData)=>{
    let Method = 'POST';
    // let header = { 
    //   'Content-Type' : 'multipart/form-data'
    // };
    let value = deckName;
    let action = 'addCard';
    const deckInfo = await fetch( 'http://localhost:3000/api/post.php?' + "deckName=" + value + "&action="+action,
      { method : Method,
      // headers: header,
      body : cardData 
      })
      .then(res => {return res.text();
      })
      .then(data => {
        console.log(data);
        // switch (data) {
        //   case 'The deck was Successfully added.':
        //     refreshDeckList();
        //     break;
        
        //   default:
        //     break;
        // }
        // return data
      }
      )
      .catch( error => console.error('Error:', error)) 
      return deckInfo;
  }

  const getAllCards = async(deckName)=>{
    if (deckName)
    {
      var requestOptions = {
        method: 'GET',
        headers: { 
          'Content-Type' : 'application/json'
        }
      };
      var url = 'http://localhost:3000/api/test.php?action=Cards&deckName=' + deckName;
      const cards = await runFetch(url, requestOptions);
    //   const result = fetch(url, requestOptions)
    // .then(res=>res.json())
    // .then(data=>{return data})
    // .catch(error => console.log('error', error));
      return cards;
    }
  }

  const runFetch = (url, requestOptions)=>{
    const result = fetch(url, requestOptions)
    .then(res=>res.json())
    .then(data=>{return data})
    .catch(error => console.log('error', error));
    return result;
  }
  
  const loadDeck = (cards, i = 0)=>{
    if(i < cards.length){

    let container = document.getElementById('mainCenterDisplay');
    container.innerHTML = `
    <div class="cardElContainer" id="cardElContainer">  
      <div class="cardEl" id="cardEl">
        <div class="cardEl_Header" id="cardEl_Header">
          <button type="button" class="iconBtn" id="cardEl_Header_EditBtn">edit</button>
          <button type="button" class="iconBtn" id="cardEl_Header_ImpBtn">!</button>
          <button type="button" class="iconBtn" id="cardEl_Header_FavBtn">fav</button>
        </div>
        <div class="cardEl_Frontside" id="cardEl_Content">
          ${cards[i]['frontContent']};
        </div>
      </div>
      <div class="cardElControls" id="cardElControls">
        <div class="cardElControls_Left" id = "cardElControls_Left">
          <button type="button" class="iconBtn" id="cardEl_Control_SettingBtn">setting</button>
        </div>
        <div class="cardElControls_Center" id = "cardElControls_Center">
          <button type="button" class="iconBtn" id="cardEl_Control_BeforeBtn">before</button>
          <span id="cardEl_Control_CardNr">${i+1}/${cards.length}</span>
          <button type="button" class="iconBtn" id="cardEl_Control_NextBtn">next</button>
        </div>
        <div class="cardElControls_Right" id = "cardElControls_Right">
          <button type="button" class="iconBtn" id="cardEl_Control_ShuffleBtn">shuffle</button>
          <button type="button" class="iconBtn" id="cardEl_Control_FullScrBtn">fullscreen</button>
        </div>
      </div>
    </div>
    `
    const cardContent = document.getElementById('cardEl_Content');
    const nextBtn = document.getElementById('cardEl_Control_NextBtn');
    const beforeBtn = document.getElementById('cardEl_Control_BeforeBtn');
    const favBtn = document.getElementById('cardEl_Control_NextBtn');
    const editBtn = document.getElementById('cardEl_Control_NextBtn');
    const importantBtn = document.getElementById('cardEl_Control_NextBtn');
    const settingBtn = document.getElementById('cardEl_Control_SettingBtn');
    const shuffleBtn = document.getElementById('cardEl_Control_ShuffleBtn');
    const fullScrBtn = document.getElementById('cardEl_Control_FullScrBtn');

    nextBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      i++;
      loadDeck(cards, i)
    })

    beforeBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      i--;
      loadDeck(cards, i)
    })

    cardContent.addEventListener('click', (e)=>{
      if(e.target.tagName !== 'button') 
      {
        if (e.target.classList.contains('cardEl_Frontside'))
        {
          loadBackside(cards[i]);
        } else {
          loadFrontside(cards[i]);
        }
        
        
        
      }
    })

  }
  }

  const loadBackside = (cardData)=>{
    const cardFront = document.getElementById('cardEl_Content');
    cardFront.classList.toggle('cardEl_Frontside');
    cardFront.classList.toggle('cardEl_Backside');
    cardFront.innerHTML =`
    ${cardData['backContent']}
    `

  }

  const loadFrontside = (cardData)=>{
    const cardFront = document.getElementById('cardEl_Content');
    cardFront.classList.toggle('cardEl_Frontside');
    cardFront.classList.toggle('cardEl_Backside');
    cardFront.innerHTML =`
    ${cardData['frontContent']}
    `

  }  

  showDecks();
})

function toggle(e){
  // let target = e.target.getAttribute('toggle-target');
  let target = e.target.closest('button').getAttribute('toggle-target')
  console.log (target)

  let element = document.getElementById(target);
  console.log (element.classList)
  element.classList.toggle('show');
}


