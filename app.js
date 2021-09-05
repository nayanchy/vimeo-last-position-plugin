let shortCodeUse = videodata.class.length

for(let i=0; i<shortCodeUse; i++){
    let vidClass    = `.${videodata.class[i]}`
    let vid         = document.querySelector(vidClass)
    
    let player  = new Vimeo.Player(vid)

    let currentTime = 0

    player.on('timeupdate', function(data){
        currentTime = data.seconds
        localStorage.setItem(videodata.vid[i], currentTime)
    })

    let lastWatchedTime = localStorage.getItem(videodata.vid[i])
    
    if(lastWatchedTime){
       player.setCurrentTime(lastWatchedTime)
    }
}

const scrollPosition = function(){
    const currUrl = String(window.location.href)
    const urlSplit = currUrl.split('/')
    const urlSlug = urlSplit[urlSplit.length - 2]

    let scroll_y = '';

    window.addEventListener("scroll", function(event) { 
        scroll_y = this.scrollY; 
        console.log(scroll_y); 
        this.document.cookie = `${urlSlug} = ${scroll_y}`;
    }); 
}
scrollPosition()

const scrollToLastPosition = function(){
    if(lastpositiondata){
        const lastPosBtn = document.querySelector('#lastscreen')
        const lastPosition = lastpositiondata.lastPosition

        lastPosBtn.addEventListener('click', function(){
            window.scrollTo(0, lastPosition)
        })
    }
}

scrollToLastPosition()