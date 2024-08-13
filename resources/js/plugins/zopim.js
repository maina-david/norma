window.initLiveChat = () => {
  window.startLiveChat = (name, email, organisations, phoneMobile, mobileCountryCode) => {
    const chat = window.$zopim.livechat;
    chat.setEmail(email);
    chat.setName(name);
    if (phoneMobile) {
      chat.setPhone(`${mobileCountryCode} ${phoneMobile}`);
    }
    if (organisations && organisations.length > 0) {
      organisations.forEach(o => {
        chat.addTags(o);
      });
    }

    chat.window.show();
    // chat.window.openPopout();
  };
};

initLiveChat();
