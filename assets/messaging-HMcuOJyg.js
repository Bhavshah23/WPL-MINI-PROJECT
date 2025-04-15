import"./modulepreload-polyfill-B5Qt9EMX.js";const n=[{id:1,name:"Priya Jain",avatar:"/placeholder.svg?height=100&width=100&text=PJ",status:"Online",lastSeen:"Just now",unreadCount:3,messages:[{id:1,text:"Hi there! How are you doing today?",timestamp:"10:30 AM",sent:!1},{id:2,text:"I'm doing well, thanks for asking! How about you?",timestamp:"10:32 AM",sent:!0},{id:3,text:"I'm great! Just wanted to check if you're free this weekend for the Jain community event?",timestamp:"10:33 AM",sent:!1},{id:4,text:"Yes, I'm planning to attend. Would be nice to meet you there!",timestamp:"10:35 AM",sent:!0},{id:5,text:"Perfect! Looking forward to it. I'll be wearing a blue dress so you can spot me easily.",timestamp:"10:36 AM",sent:!1}]},{id:2,name:"Rahul Jain",avatar:"/placeholder.svg?height=100&width=100&text=RJ",status:"Last seen 2 hours ago",lastSeen:"2 hours ago",unreadCount:0,messages:[{id:1,text:"Hey, have you tried that new vegetarian restaurant downtown?",timestamp:"Yesterday",sent:!1},{id:2,text:"Not yet, but I've heard good things about it. Is it fully Jain-friendly?",timestamp:"Yesterday",sent:!0},{id:3,text:"Yes, they have a separate Jain menu with no root vegetables. The food is amazing!",timestamp:"Yesterday",sent:!1}]},{id:3,name:"Anjali Jain",avatar:"/placeholder.svg?height=100&width=100&text=AJ",status:"Online",lastSeen:"Just now",unreadCount:1,messages:[{id:1,text:"Hello! I saw we matched yesterday. I noticed we both enjoy meditation.",timestamp:"2 days ago",sent:!1},{id:2,text:"Hi Anjali! Yes, I've been practicing meditation for about 3 years now. How about you?",timestamp:"2 days ago",sent:!0},{id:3,text:"That's wonderful! I've been meditating since childhood. My grandfather taught me. Would love to share techniques sometime.",timestamp:"2 days ago",sent:!1},{id:4,text:"I'd really like that. Maybe we could meet at the Jain temple this weekend?",timestamp:"2 days ago",sent:!0},{id:5,text:"Just checking if we're still on for this weekend?",timestamp:"Just now",sent:!1}]},{id:4,name:"Vikram Jain",avatar:"/placeholder.svg?height=100&width=100&text=VJ",status:"Last seen yesterday",lastSeen:"Yesterday",unreadCount:0,messages:[{id:1,text:"Hi, I'm organizing a charity event for our community next month. Would you be interested in volunteering?",timestamp:"3 days ago",sent:!1},{id:2,text:"That sounds like a great initiative! I'd love to help. What kind of volunteers do you need?",timestamp:"3 days ago",sent:!0},{id:3,text:"We need people to help with food distribution and organizing activities for children. Your profile mentions you're good with kids!",timestamp:"3 days ago",sent:!1},{id:4,text:"Yes, I'd be happy to help with the children's activities. Please send me more details when you have them.",timestamp:"3 days ago",sent:!0}]},{id:5,name:"Meera Jain",avatar:"/placeholder.svg?height=100&width=100&text=MJ",status:"Online",lastSeen:"Just now",unreadCount:2,messages:[{id:1,text:"Hello! I noticed we both love traveling. What's your favorite place you've visited?",timestamp:"1 week ago",sent:!1},{id:2,text:"Hi Meera! My favorite place would have to be Palitana in Gujarat. The Jain temples there are breathtaking. How about you?",timestamp:"1 week ago",sent:!0},{id:3,text:"I love Palitana too! But my favorite is actually Udaipur. The lakes and palaces are so peaceful.",timestamp:"1 week ago",sent:!1},{id:4,text:"Udaipur is on my list! Would love to hear more about your experiences there.",timestamp:"1 week ago",sent:!0},{id:5,text:"I'd love to tell you all about it! Maybe over coffee sometime?",timestamp:"Today",sent:!1},{id:6,text:"There's this great Jain-friendly café I know that serves amazing vegan desserts too!",timestamp:"Just now",sent:!1}]}];class d{constructor(){this.users=n,this.selectedUserId=null,this.userListElement=document.getElementById("user-list"),this.chatMessagesElement=document.getElementById("chat-messages"),this.selectedUserNameElement=document.getElementById("selected-user-name"),this.selectedUserStatusElement=document.getElementById("selected-user-status"),this.messageInputElement=document.getElementById("message-input"),this.sendButtonElement=document.getElementById("send-button"),this.sidebarElement=document.getElementById("sidebar"),this.toggleSidebarButton=document.getElementById("toggle-sidebar"),this.backButton=document.getElementById("back-button"),this.chatHeaderElement=document.getElementById("chat-header"),this.init()}init(){this.renderUserList(),this.setupEventListeners()}renderUserList(){this.userListElement.innerHTML="",this.users.forEach(t=>{const e=t.messages[t.messages.length-1],s=document.createElement("div");s.className=`user-item ${this.selectedUserId===t.id?"active":""}`,s.dataset.userId=t.id,s.innerHTML=`
        <div class="user-avatar">
          <img src="${t.avatar}" alt="${t.name}">
        </div>
        <div class="user-info">
          <div class="user-name">${t.name}</div>
          <div class="user-last-message">${e?e.text:"No messages yet"}</div>
        </div>
        <div class="user-meta">
          <div class="message-time">${e?e.timestamp:""}</div>
          ${t.unreadCount>0?`<div class="unread-count">${t.unreadCount}</div>`:""}
        </div>
      `,this.userListElement.appendChild(s)})}renderChatMessages(t){const e=this.users.find(a=>a.id===t);if(!e)return;this.chatMessagesElement.innerHTML="",e.messages.forEach(a=>{const i=document.createElement("div");i.className=`message ${a.sent?"message-sent":"message-received"}`,i.innerHTML=`
        <div class="message-content">${a.text}</div>
        <div class="message-timestamp">${a.timestamp}</div>
      `,this.chatMessagesElement.appendChild(i)}),this.chatMessagesElement.scrollTop=this.chatMessagesElement.scrollHeight,this.selectedUserNameElement.textContent=e.name,this.selectedUserStatusElement.textContent=e.status;const s=document.querySelector(".selected-user-avatar");s.innerHTML=`<img src="${e.avatar}" alt="${e.name}">`,this.messageInputElement.disabled=!1,this.sendButtonElement.disabled=!1,e.unreadCount=0,this.renderUserList()}selectUser(t){this.selectedUserId=Number.parseInt(t),this.renderUserList(),this.renderChatMessages(this.selectedUserId),window.innerWidth<=768&&this.sidebarElement.classList.remove("active")}sendMessage(t){if(!t.trim()||!this.selectedUserId)return;const e=this.users.find(a=>a.id===this.selectedUserId);if(!e)return;const s={id:e.messages.length+1,text:t,timestamp:this.getCurrentTime(),sent:!0};e.messages.push(s),this.renderChatMessages(this.selectedUserId),this.renderUserList(),this.messageInputElement.value=""}getCurrentTime(){const t=new Date;let e=t.getHours();const s=t.getMinutes(),a=e>=12?"PM":"AM";return e=e%12,e=e||12,`${e}:${s<10?"0"+s:s} ${a}`}toggleSidebar(){this.sidebarElement.classList.toggle("active")}setupEventListeners(){this.userListElement.addEventListener("click",t=>{const e=t.target.closest(".user-item");if(e){const s=Number.parseInt(e.dataset.userId);this.selectUser(s)}}),this.sendButtonElement.addEventListener("click",()=>{this.sendMessage(this.messageInputElement.value)}),this.messageInputElement.addEventListener("keypress",t=>{t.key==="Enter"&&this.sendMessage(this.messageInputElement.value)}),this.toggleSidebarButton.addEventListener("click",()=>{this.toggleSidebar()}),this.backButton.addEventListener("click",()=>{this.toggleSidebar()}),window.addEventListener("resize",()=>{window.innerWidth>768&&this.sidebarElement.classList.remove("active")})}}document.addEventListener("DOMContentLoaded",()=>{new d});
