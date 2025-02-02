document.addEventListener('DOMContentLoaded', function(){

  //toggle icons in Navbar
  const assignToggleToNavabr = () => {
    const navbarIcons = document.querySelectorAll("button.dropdown-toggle");
    navbarIcons.forEach((navbarIcon, index) => {
      switch (index) {
        case 0:
          navbarIcon.setAttribute('toggle-target', 'categoryDropdown');
          break;
        case 1:
          navbarIcon.setAttribute('toggle-target', 'deckDropdown');
          break;
        case 2:
          navbarIcon.setAttribute('toggle-target', 'learnDropdown');
          break;
        case 3:
          navbarIcon.setAttribute('toggle-target', 'searchDropdown');
          break;
        case 4:
          navbarIcon.setAttribute('toggle-target', 'userDropdown');
          break;  
        case 5:
          navbarIcon.setAttribute('toggle-target', 'userDropdown');
          break;  
        default:
          navbarIcon.setAttribute('toggle-target', 'userDropdown');
          break;
      }
      navbarIcon.addEventListener('click', toggle);
    })

  }


  const addDeckBtn = document.getElementById('add_Deck__Btn');
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
      fetch( 'http://localhost:3000/api/index.php?action=' + action,
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
            var decksArr = await getDecksArr();
            await showDecks(decksArr);
            let deckName = deckData.get('deckName__input');
            selectDeck(deckName);

            
            
            showDeckDetails(data[0]);
            break;
        
          case 'There are no card found for this Deck':
            console.log(data);
           
            default:
            console.log(data)
            break;
          }
        }
      )
      .catch( error => console.error('Error:', error)) 
    })   
  });

  const searchDeckInput = document.getElementById('deckSearch');

  searchDeckInput.addEventListener('keyup', async function(){
    let searchTerm = escapeHtml(searchDeckInput.value);
    let searchCardsChkBx = document.getElementById('searchCardsChkBx');
    if (searchCardsChkBx.checked)
    {
      // check for valid searchterm
      const searchterm = this.value;
      if (!searchterm) {
        const container = document.getElementById('mainCenterDisplay');
        container.innerHTML = '';

      }else{
        let searchResult = await searchCards(searchTerm);
        showCardSearch(searchResult, searchTerm);
      }
      
    } else
    {
      let deckArr = await getDecksArr();
      const matches = deckArr.filter(s => s.includes(searchTerm));
      showDecks(matches)   
    }
    
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

  const  showDecks = async(decksArr) =>
  {
    const container = document.getElementById('decksNameList');
    const deckList = document.createElement('ul');
    //empty container
    container.innerHTML = '';
    if (decksArr)
    {
      Object.entries(decksArr).forEach (([deckName,cards]) => {
        const liEl = document.createElement('li');
        liEl.classList.add('subContainer', 'tree');
        liEl.setAttribute('data_id', cards[0]['deckId'])

        liEl.innerHTML = `
          
          <p class = "deckName" value="${deckName}">
          ${deckName}
          </p>
          
        `;

        liEl.addEventListener('click', selectLi);
        //   e.stopPropagation();
          
        //   selectLi(e.target);
  
        // });
  
  
        deckList.append(liEl);
  
      
      })
      container.append(deckList);

        
      // for (let i = 0; i < decksArr.length; i++) {
      //   const liEl = document.createElement('li');
      //   liEl.classList.add('subContainer', 'tree');

      //   liEl.innerHTML = `
          
      //     <p class = "deckName" value="${decksArr[i]}">
      //     ${decksArr[i]}
      //     </p>
          
      //   `;

      //   liEl.addEventListener('click', (e) => {
      //     e.stopPropagation();
          
      //     selectLi(e.target);
  
  
  
      //   });
  
  
      //   deckList.append(liEl);
  
      
      // }
      // container.append(deckList);

    }    
  }

  const selectLi = async (e)=> 
  {
    const selectLi = e.target.closest('li');
    const parentUl = selectLi.closest('ul');

    //clears the last selected li
    const selectedEls = parentUl.querySelectorAll("li.selected");
    (selectedEls.length > 0) ? selectedEls.forEach(selectedEl => selectedEl.classList.remove('selected')) : null;      
    await showSelectedLi(selectLi)
    // //selects li
    // selectLi.classList.add('selected');
    // //get deck name
    // const deckName = selectLi.querySelector('.deckName').innerText;
    // //get deck info from DB
    // const deckInfo = await getDeckDetails(deckName);
    // //show deck info 
    // if (deckInfo && deckInfo.length === 1){
    //   showDeckDetails(deckInfo[0])
    // }else{
    //   console.log("Error, no such Deck was found.")
    // }
  };

  const showSelectedLi = async (liElement) => {
    //selects li
    liElement.classList.add('selected');
    //get deck name
    // const deckName = liElement.querySelector('.deckName').innerText;
    const deckId = liElement.getAttribute('data_id')
    //get deck info from DB
    const deckInfo = await getDeckDetails(deckId);
    //show deck info 
    if (deckInfo && deckInfo.length === 1){
      showDeckDetails(deckInfo[0])
    }else{
      console.log("Error, no such Deck was found.")
    }
  }

  const getDeckDetails = async (deckID) => {
    const Method = 'GET';
    const header = { 
      'Content-Type' : 'application/json'
    };

    const sanitiseddeckID = sanitizeText(deckID);
    const action = 'getDeckData';
    
    const deckInfo = await fetch(`api/index.php?deckID=${sanitiseddeckID}&action=${action}`,
      { method : Method,
      headers: header
      // body : JSON.stringify(deckData)
      })
      .then(res => {
        switch (res.status) {
          case 200:
            return res.json();
            break;
          case 404:
            throw new Error('Resource not found');
            break;
          case 400:
            throw new Error('Bad Request');
            break;
          default:
            throw new Error(`Unexpected error: ${response.status}`);
            break;
          }
        })
      // .then(data => data)
      .catch( error => console.error('Error:', error)) 
      return deckInfo;  
    } 
  

  const showDeckDetails = (info) => {
      const viewContainer = document.getElementById('mainCenterDisplay');
      viewContainer.innerHTML = `
      <div class='deck-Info' id="deckInfo">
      
        <div class="deck-Info_Header">
          <div class="deck-Info_Header_Left">
          <p> Deck Name: <span class="data">${info.deckName}</span></p>
            <p>Created <span class = "data">${info.timeCreated} </span></p>
            <p>Last edited <span class = "data">${info.timeEdited}</span></p>
          </div>
          <div>
          <p>Category : <span class = "data">${info.deckCategory}</span></p>

          <p> Deck description: <span class="data">${info.deckDescription}</span></p>
          </div> 
          <div class="deck-Info_Header_Right">
            <span class="material-symbols-outlined">
              share
            </span>
          </div>
        </div>

        

        <div class="deck-Info_Content">
          <div class="deck-Info_Content_Number">
          <p>Total cards : <span class = "data">${info.totalCards}</span></p>

          </div>
          <div class="deck-Info_Content_Masetery">
          <p>Mastery : <span class = "data">${info.mastery}%</span></p>

          </div>
          <div class="deck-Info_Content_Record">
          <p>Best time : <span class = "data">${info.record}</span></p>

          </div>
        </div>

        <div class="deck-Info_Footer">
          <div class="deck-Info_Footer_Actions " id ="deck-Info_Footer_Actions__div">
            <button type="button" class="menu__btn addCardBtn" id= "addCardBtn_deck-Info_Footer_Actions" >Add card</button>
            <button type="button" class="menu__btn learnBtn" id= "learnBtn_deck-Info_Footer_Actions">Learn</button>
            <button type="button" class="menu__btn testBtn" id= "testBtn_deck-Info_Footer_Actions">Test</button>
            <button type="button" class="cancelBtn menu__btn deleteBtn" id= "deleteBtn_deck-Info_Footer_Actions">Delete</button>
            <button type="button" class="menu__btn settingBtn" id= "settingBtn_deck-Info_Footer_Actions">Setting</button>
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
        if (cards) {
          loadDeck(cards);
        } else {

        }
        console.log(cards)

      })

      testBtn.addEventListener('click',async (e)=>{
        e.preventDefault();
        let cards = await getAllCards(info.deckName);
        loadDeck(cards);

      })

      settingBtn.addEventListener('click', ()=>{
        console.log(cards);
      })


  }


  const loadEmptyCard = (deckName)=>{
    const viewContainer = document.getElementById('mainCenterDisplay');
      viewContainer.innerHTML =`
        <form method="post" id="emptyCardForm" class="emptyCardForm">

          <div class="emptyCardHeader">
            <button type="button" class= " menu__btn cardSaveBtn" id = "cardsSaveBtn"> Done </button>
            <span class="emptyCardHeader_DeckName">${deckName}</span>
            <button type="submit" class= "menu__btn addNewCardBtn" id= "addNewCardBtn"> + </button>
          </div>

          <div class="emptyCardFront">
            <textarea name="cardFrontText" id="emptyCardFrontText" cols="50" rows="10" placeholder=" Front of the card"></textarea>
          </div>

          <div class="emptyCardBack">
          <textarea name="cardBackText" id="emptyCardBackText" cols="50" rows="10" placeholder=" Back of the card"></textarea>
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
        const cardData = new FormData(document.getElementById('emptyCardForm'));
        addCardToDeck(deckName, cardData);
        selectDeck(deckName);
      })
  }

  const selectDeck= (deckName)=>{
    const addedDeckLiEl = document.querySelector('div.main-Left_Cards ul').children;
    for (const liEl of addedDeckLiEl) {
      

      if (liEl.lastElementChild.innerText == deckName) {
        showSelectedLi(liEl);
      }  
    }
  }

  const addCardToDeck = async(deckName, cardData)=>{
    
    //check if cardData has any inputs
    cardData.forEach((value, key) => {
      if(!value){
        return
      }
    })
      
    //saves carddata if input exists
    const Method = 'POST';
    // let header = { 
    //   'Content-Type' : 'multipart/form-data'
    // };
    const value = deckName;
    const action = 'addCard';
    const deckInfo = await fetch( 'http://localhost:3000/api/index.php?' + "deckName=" + value + "&action="+action,
      { method : Method,
      // headers: header,
      body : cardData 
      })
      .then(res => res.text()
      )
      .then(data => {
        console.log(data);
        return data
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
      var url = 'http://localhost:3000/api/index.php?action=getCards&deckName=' + deckName;
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
    // .then(res=>res.json())
    .then(res => {
      if (res.ok) {
        return res.json();
      }

      throw new Error (res.statusText)
      
    })
    .then(data=>{return data})
    .catch(error => tinderModal(error));
    return result;
  }

  const runtest = (url, requestOptions)=>{
    const result = fetch(url, requestOptions)
    .then(res=>res.text())
    .then(data=>{return data})
    .catch(error => console.log('error', error));
    return result;
  }
   
  const tinderModal = (text)=>{
    // instanciate new modal
    var modal = new tingle.modal({
      footer: true,
      stickyFooter: false,
      closeMethods: ['overlay', 'button', 'escape'],
      closeLabel: "Close",
      cssClass: ['custom-class-1', 'custom-class-2'],
      onOpen: function() {
          console.log('modal open');
      },
      onClose: function() {
          console.log('modal closed');
      },
      beforeClose: function() {
          // here's goes some logic
          // e.g. save content before closing the modal
          return true; // close the modal
          return false; // nothing happens
  }
    });
    // set content
    modal.setContent(`<p>${text}<p>`);
    console.log(modal);
    
      // add a button
    modal.addFooterBtn('Button label', 'tingle-btn tingle-btn--primary', function() {
      // here goes some logic
      modal.close();
    });

    // add another button
    modal.addFooterBtn('Dangerous action !', 'tingle-btn tingle-btn--danger', function() {
      // here goes some logic
      modal.close();
    });

    // open modal
    modal.open();

    // close modal
    // modal.close();
  }
  
  const loadDeck = (cards, i = 0)=>{
    if(0<=i && i < cards.length)
    {

      let container = document.getElementById('mainCenterDisplay');
      container.innerHTML = `
      <div class="cardElContainer" id="cardElContainer">  
        <div class="cardEl" id="cardEl">
          <div class="cardEl_Header" id="cardEl_Header">
            <button type="button" class="menu__btn" id="cardEl_Header_EditBtn">edit</button>
            <button type="button" class="menu__btn" id="cardEl_Header_DelBtn">Delete</button>
            <button type="button" class="menu__btn" id="cardEl_Header_FavBtn">fav</button>
          </div>
          <div class="cardEl_Frontside" id="cardEl_Content" number="${i}">
            ${cards[i]['frontContent']}
          </div>
        </div>
        <div class="cardElControls" id="cardElControls">
          <div class="cardElControls_Left" id = "cardElControls_Left">
            <button type="button" class="menu__btn" id="cardEl_Control_SettingBtn">setting</button>
          </div>
          <div class="cardElControls_Center" id = "cardElControls_Center">
            <button type="button" class="menu__btn" id="cardEl_Control_BeforeBtn">
              <span class="material-symbols-outlined">
                navigate_before
              </span>
            </button>
            <span id="cardEl_Control_CardNr">${i+1}/${cards.length}</span>
            <button type="button" class="menu__btn" id="cardEl_Control_NextBtn">
              <span class="material-symbols-outlined">
                navigate_next
              </span>
            </button>
          </div>
          <div class="cardElControls_Right" id = "cardElControls_Right">
            <button type="button" class="menu__btn" id="cardEl_Control_ShuffleBtn">shuffle</button>
            <button type="button" class="menu__btn" id="cardEl_Control_FullScrBtn">fullscreen</button>
          </div>
        </div>
      </div>
      `
      const cardContent = document.getElementById('cardEl_Content');
      const nextBtn = document.getElementById('cardEl_Control_NextBtn');
      const beforeBtn = document.getElementById('cardEl_Control_BeforeBtn');
      const favBtn = document.getElementById('cardEl_Header_FavBtn');
      const editBtn = document.getElementById('cardEl_Header_EditBtn');
      const deleteBtn = document.getElementById('cardEl_Header_DelBtn');
      const settingBtn = document.getElementById('cardEl_Control_SettingBtn');
      const shuffleBtn = document.getElementById('cardEl_Control_ShuffleBtn');
      const fullScrBtn = document.getElementById('cardEl_Control_FullScrBtn');

      editBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        cardNr = Number(cardContent.getAttribute('number'));
        editCard(cards, cardNr);
      })

      shuffleBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        const shuffledDeck = shuffleArray(cards);
        loadDeck(shuffledDeck);
      })

      favBtn.addEventListener('click', async function(e){
        e.preventDefault();
        const newFav = (cards[i]['fav']) ? 0 : 1;
        const result = await updateCard(cards[i], 'fav', newFav);
        console.log(result);
        // loadDeck(shuffledDeck);
      })

      deleteBtn.addEventListener('click',async (e)=>{
        e.preventDefault();
        cardNr = Number(cardContent.getAttribute('number'));
        if (confirm('do you want to delete this card?')){
          const deckName = document.querySelector('#decksNameList ul li.selected p.deckName').getAttribute('value');
          const cardId = cards[cardNr]['cardId'];
          const result = await deleteCard(cards, cardId, deckName);

          if (result)
          {

            let newCards = await getAllCards(deckName);
            loadDeck(newCards, cardNr)

          }
        }
      })

      nextBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        i++;
        if (0<=i && i<cards.length)
        {
          loadDeck(cards, i)
        } else if (i = cards.length) {
          i = cards.length - 1;
        }
      })

      beforeBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        i--;
        if (0<=i && i<cards.length)
        {
          loadDeck(cards, i)
        }else if (i < 0) {
          i = 0;
        }
      })

      cardContent.addEventListener('click', (e)=>{
        if(e.target.tagName !== 'button') 
        {
          if (e.target.classList.contains('cardEl_Frontside'))
          {
            loadBackside(cards, i);
          } else {
            loadFrontside(cards[i]);
          }
          
          
          
        }
      })

      settingBtn.addEventListener('click', async()=>{
        const deckName = document.querySelector('#decksNameList ul li.selected p.deckName').getAttribute('value');
        let result = await updateDeck(deckName, cards);
        console.log(result);
      })



    }else if(i >= cards.length)
    {
      i = cards.length -1;
      loadDeck(cards, i);
    }
  }

  const loadBackside = (cards, i)=>{
    const cardFront = document.getElementById('cardEl_Content');
    cardFront.classList.toggle('cardEl_Frontside');
    cardFront.classList.toggle('cardEl_Backside');
    cardFront.innerHTML =`
    <div class ="backsideContent" id="backsideContent">
    ${cards[i]['backContent']}
    </div>
    <div class="diffSelector" id="diffSelector">
      <span>How difficult waas it to remember?</span>
      <div class="diffSelector_Div" id="diffSelector_Div">
        
        <div class="diff_Div" id="diff_div1">
          <button class="menu__btn" id="diff_Btn1" value ='1'>1</button>
        </div>
        <div class="diff_Div" id="diff_div2">
          <button class="menu__btn" id="diff_Btn2" value ='2'>2</button>
        </div>
        <div class="diff_Div" id="diff_div3">
          <button class="menu__btn" id="diff_Btn3" value ='3'>3</button>
        </div>
        <div class="diff_Div" id="diff_div4">
          <button class="menu__btn" id="diff_Btn4" value ='4'>4</button>
        </div>
        <div class="diff_Div" id="diff_div5">
          <button class="menu__btn" id="diff_Btn5" value ='5'>5</button>
        </div>
      </div>
    </div>
  
    `
    const diffBtnsContainer = document.getElementById('diffSelector_Div');
    const diffBtns = diffBtnsContainer.querySelectorAll('button.menu__btn');
    diffBtns.forEach(element => {
      if (!diffBtns.hasOwnProperty(element)){
      element.addEventListener('click', async function(e){
        const diffLvl = Number(this.value);
        cards[i]['difficulty'] = diffLvl;
        const diffUpdateResult = await updateCard(cards[i], 'difficulty', cards[i]['difficulty']);
        console.log(diffUpdateResult);
      })
      }
      });
      // for (var key in diffBtns){
      //   console.log(diffBtns);
      //   if (!diffBtns.hasOwnProperty(key)){

        // key.addEventListener('click', (e)=>{
        //   let diffLvl = Number(e.target.value);
        //   cards[i]['difficulty'] = diffLvl;
          
        // })
      // }

    // }
    console.log(cards)
  }

  
  const loadFrontside = (cardData)=>{
    const cardFront = document.getElementById('cardEl_Content');
    cardFront.classList.toggle('cardEl_Frontside');
    cardFront.classList.toggle('cardEl_Backside');
    cardFront.innerHTML =`
    ${cardData['frontContent']}
    `

  }  

  const updateDeck = async(deckName, cards)=>{
    if (deckName)
    {
      let requestOptions = {
        method: 'POST',
        body: JSON.stringify(cards),
        // body:cards,
        headers: { 
          'Content-Type' : 'application/json'
        }
      };
      var url = 'http://localhost:3000/api/index.php?action=updateCards&deckName=' + deckName;
      const result = await runFetch(url, requestOptions);
    //   const result = fetch(url, requestOptions)
    // .then(res=>res.json())
    // .then(data=>{return data})
    // .catch(error => console.log('error', error));
      return result;
    }
  }

  const updateCard = async(card, variable = '' , value = '')=>{
    if (card)
    {
      let requestOptions = {
        method: 'POST',
        body: JSON.stringify(data = 
          {
            card : card,
            variable : variable,
            value : value
          }),
        // body:cards,
        headers: { 
          'Content-Type' : 'application/json'
        }
      };
      var url = 'http://localhost:3000/api/index.php?action=updateCard';
      const result = await runFetch(url, requestOptions);
    //   const result = fetch(url, requestOptions)
    // .then(res=>res.json())
    // .then(data=>{return data})
    // .catch(error => console.log('error', error));
      
      return result;
    }
  }

  const editCard = (cards, cardNr)=>{
    const cardCont = document.getElementById('cardEl');
    cardCont.innerHTML =`
      <form method="post" id="emptyCardForm" class="emptyCardForm">

        <div class="emptyCardHeader">
          <button type="button" class= "Btn cancelEditBtn" id = "cancelEditBtn"> cancel </button>
          <span class="emptyCardHeader_DeckName">${cards[cardNr]['deckName']}</span>
          <button type="submit" class= "Btn saveCardBtn" id= "saveCardBtn"> save </button>
        </div>

        <div class="emptyCardFront" align="center">
          <textarea name="cardFrontText" id="emptyCardFrontText" cols="30" rows="10" placeholder="Front of the card">
            ${cards[cardNr]['frontContent']}
          </textarea>
        </div>

        <div class="emptyCardBack">
        <textarea name="cardBackText" id="emptyCardBackText" cols="30" rows="10" placeholder="Back of the card">
          ${cards[cardNr]['backContent']}
        </textarea>
        </div>
        
      </form>
    `
    const saveCardBtn =  document.getElementById('saveCardBtn');
    const cancelEditBtn =  document.getElementById('cancelEditBtn');
    

    saveCardBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      const cardData = new FormData(document.getElementById('emptyCardForm'));
      cards[cardNr]['frontContent'] =  escapeHtml(cardData.get('cardFrontText'));
      cards[cardNr]['backContent'] = escapeHtml(cardData.get('cardBackText'));
      loadDeck(cards, cardNr);
      
      
    })

    cancelEditBtn.addEventListener ( 'click', (e)=>{
      e.preventDefault();
      // const cardData = new FormData(document.getElementById('emptyCardForm'));
      // addCardToDeck(deckName, cardData);
      // selectDeck(deckName);
      loadDeck(cards, cardNr);

    })
    
  }

  const deleteCard = async (cards, cardId, deckName)=>{
    const url = 'http://localhost:3000/api/index.php?action=deleteCard&deckName=' + deckName + "&cardId=" + cardId;
    const requestOptions = {
      method:'post',
      body: JSON.stringify(cards),
      headers:{
        'Content-Type' : 'application/json'      }
    }
    let response = await runFetch(url,requestOptions);
    console.log(response);
    if (response = 'delete successfull')
    {
      return true;
    }else
    {
      return false;
    }
       
  }

  const searchCards = async (searchTerm)=>{
    const url = 'http://localhost:3000/api/index.php?action=searchCards&searchTerm=' + searchTerm;
    const requestOptions = {
      method:'get',
      headers:{
        'Content-Type' : 'application/json'      }
    }
    
    const results = await runFetch (url, requestOptions);
    return results;
  }

  const showCardSearch = (searchResult, searchTerm)=>{

    if ( !isEmptyObject(searchResult))
    {
      const displayCont = document.getElementById('mainCenterDisplay');
      displayCont.style.flexDirection = "column";
      const displayFrontResultsCont = document.createElement('div');
      displayFrontResultsCont.classList.add("cards_Cont");
      const displayBackResultsCont = document.createElement('div');
      displayBackResultsCont.classList.add("cards_Cont");

      displayCont.innerHTML= `
        <div class="card_header">
          The search results for "${searchTerm}" are :
        </div>
      `
      
      if (Object.keys(searchResult['frontContent']).length == 0)
      {
        displayFrontResultsCont.innerHTML = `
          <span class="cards_Cont">
            No match found in front side of decks
          </span>
        `
      }else{
        displayFrontResultsCont.innerHTML=`
        Found on front side : 
        `
        for (key in searchResult['frontContent'])
        {
          if (!isEmptyObject(searchResult['frontContent'][key]))
          {
            var displayFrontSpan = document.createElement('div');
            displayFrontSpan.classList.add("card_search");
            displayFrontSpan.innerHTML = `
                <span class = "cards_Cont">
                  Deck: "${key}"
                </span>
            `
            for (card in searchResult['frontContent'][key])
            {
              
              var searchCard = displaycard (searchResult['frontContent'][key][card] , 'frontContent');
              displayFrontSpan.append(searchCard);

              searchCard.addEventListener('click', function(e){
                // const card1 = searchResult['frontContent'][key][card];
                // console.log(searchResult['frontContent'][key][card]);
                enlarge (searchResult['frontContent'][key][card]);
              });
            }
          }
          displayFrontResultsCont.append(displayFrontSpan);

        }
      }
      
      if (Object.keys(searchResult['backContent']).length == 0)
      {
        displayBackResultsCont.innerHTML = `
          <span>
            No match found in back side of decks
          </span>
        `
      }else{
        displayBackResultsCont.innerHTML=`
        Found on back side : 
        `
        for (key in searchResult['backContent'])
        {
          {
            // if (searchResult['frontContent'].hasOwnProperty(key))
            if (!isEmptyObject(searchResult['backContent'][key]))
            {
              console.log(searchResult['backContent'][key]);
              var displayBackSpan = document.createElement('div');
              displayBackSpan.classList.add("card_search");

              displayBackSpan.innerHTML = `
                  <span class = "cards_Cont">
                    ${key}
                  </span>
              `
              for (card in searchResult['backContent'][key])
              {
                const searchCard = displaycard (searchResult['backContent'][key][card] , 'backContent');
                displayBackSpan.append(searchCard);
                

              }
            }
            displayBackResultsCont.append(displayBackSpan);
          }
        }
      }
      displayCont.append(displayFrontResultsCont);
      displayCont.append(displayBackResultsCont);

    }
  }

  const escapeHtml = (unsafe)=> {
    return unsafe.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
  }

  const isEmptyObject = (obj)=> {
    return JSON.stringify(obj) === '{}'
  }

  const displaycard = (card, side)=> {
    if (!isEmptyObject(card))
    {
      var cardCont = document.createElement('div');
      cardCont.classList.add("card_rearch_body");
      cardCont.setAttribute('id', card['cardId'] );
      cardCont.innerHTML =`
          ${card[side]}
      `
    }
    return cardCont;
  }
  
  const enlarge = (card) => {
    let modal = makeModal();
    // modal.innerHTML = `
    //   <div class = "login__Cont">
    //     <div class="login_header">
    //       <h6>Please fill out Deck information:</h6>
    //     </div>
    //     <form class="login__form" id = "add-Deck__Form" method = "post">

    //       <label class="label" for="deckName__input">Deckname: </label>
    //       <input class="deckName__input" required type="text" name="deckName__input" id="deckName__input" placeholder="Name of new deck ...">
    //       <div class="invalid-input__err">
    //         <?php echo $deckNameErr; ?>
    //       </div>

    //       <div class="category__Sec">
                 
    //         <div>
    //           <label class="label" for="category__input">Category: </label>
    //           <select name="category__input" id="category__input" required >
    //             <optgroup label="Language">
    //               <optgroup label="words">
    //                 <option value="deutsch">Deutsch</option>
    //               </optgroup>
    //               <optgroup label="expressions">
    //                 <option value="deutsch">deutsch</option>
    //               </optgroup>
    //             </optgroup>
    //             <optgroup label="General">
    //               <option value="math">Math</option>
    //             </optgroup>
    //           </select>
    //           <div class="invalid-input__err">
    //             <?php echo $categoryErr; ?>
    //           </div>
    //         </div>

    //         <div>
    //           <button type="button" class="icon__btn" id="add_Category__Btn">
    //             <span class="material-symbols-outlined">
    //               add
    //             </span>
    //           </button>
    //         </div>

    //       </div>

    //       <label class="label" for="description__input">Description</label>
    //       <textarea name="description__input" id="description__input" cols="30" rows="10" placeholder="Enter a brief explaination"></textarea>  
    //       <div class="invalid-input__err">
    //         <?php echo $descriptionErr; ?>
    //       </div> 

    //       <div class="formBtns__div">
    //         <button class= "btn" type="submit" id= "submitDeckBtn">Register</button>
    //         <button class= "btn" type="reset">Cancel</button>
    //       </div>
    //     </form>
    //   </div>
    // `
    loadCard(card , modal);
  }

  const loadCard = (card , modal) => {
    modal.innerHTML = `
    <div class="cardElContainer" id="cardElContainer">  
        <div class="cardEl" id="cardEl">
          
          <div class="cardEl_Frontside" id="cardEl_Content" >
            ${card['frontContent']}
          </div>
        </div>
        
      </div>
      `
      const cardContent = document.getElementById('cardEl_Content');
      // const nextBtn = document.getElementById('cardEl_Control_NextBtn');
      // const beforeBtn = document.getElementById('cardEl_Control_BeforeBtn');
      const favBtn = document.getElementById('cardEl_Control_FavBtn');
      const editBtn = document.getElementById('cardEl_Header_EditBtn');
      const deleteBtn = document.getElementById('cardEl_Header_DelBtn');
      // const settingBtn = document.getElementById('cardEl_Control_SettingBtn');
      // const shuffleBtn = document.getElementById('cardEl_Control_ShuffleBtn');
      // const fullScrBtn = document.getElementById('cardEl_Control_FullScrBtn');

      editBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        let cardNr = Number(cardContent.getAttribute('number'));
        editCard(cards, cardNr);
      })

      deleteBtn.addEventListener('click',async (e)=>{
        e.preventDefault();
        cardNr = Number(cardContent.getAttribute('number'));
        if (confirm('do you want to delete this card?')){
          const deckName = document.querySelector('#decksNameList ul li.selected p.deckName').getAttribute('value');
          const cardId = cards[cardNr]['cardId'];
          const result = await deleteCard(cards, cardId, deckName);

          if (result)
          {

            let newCards = await getAllCards(deckName);
            loadDeck(newCards, cardNr)

          }
        }
      })

     

      cardContent.addEventListener('click', (e)=>{
        if(e.target.tagName !== 'button') 
        {
          if (e.target.classList.contains('cardEl_Frontside'))
          {
            loadBackside(cards, i);
          } else {
            loadFrontside(cards[i]);
          }
          
          
          
        }
      })

      settingBtn.addEventListener('click', async()=>{
        const deckName = document.querySelector('#decksNameList ul li.selected p.deckName').getAttribute('value');
        let result = await updateDeck(deckName, cards);
        console.log(result);
      })



   
  
  }
  function toggle(e){
    // let target = e.target.getAttribute('toggle-target');
   const dropdownBtn = e.target.closest('button')
   const targetId = dropdownBtn.getAttribute('toggle-target')
   
   const element = document.getElementById(targetId);
 
   // check if the clicked button is already open
   let drop;
   element.classList.contains('show') ? drop = false : drop = true
   closeTabs(e);
   drop ? element.classList.toggle('show') : null;
   
 
   if (targetId === 'searchDropdown') {
     const input = document.getElementById('deckSearch')
     input.focus()
   } 
 
 }
 
 function closeTabs (e){
   const parentUl = e.target.closest('nav');
   const openTabs = [] && parentUl.querySelectorAll('.show');
   (openTabs.length > 0) ? openTabs.forEach(tab => tab.classList.toggle('show')) : null;
 }
 
 function shuffleArray(array) { 
   for (let i = array.length - 1; i > 0; i--) { 
     const j = Math.floor(Math.random() * (i + 1)); 
     [array[i], array[j]] = [array[j], array[i]]; 
   } 
   return array; 
 } 

 function sanitizeText(input) {
  return input.replace(/[^a-zA-Z0-9 ]/g, "").trim(); 
}
 assignToggleToNavabr();

 showDecks(getDecksArr());

})



