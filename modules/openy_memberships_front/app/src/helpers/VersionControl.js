class Version {
  init = () => {
    let version = window.localStorage.getItem('openy_membership_version');
    if (!version) {
      version = "0";
    }
    let memberships = window.localStorage.getItem('memberships');
    switch (version) {
      case "0":
        window.localStorage.removeItem('memberships');
        this.setVersion("1");
        break;
      case "1":    
        memberships = JSON.parse(memberships);
        // Clear family members keys as we will use uuid.
        memberships.family = {};
        window.localStorage.setItem('memberships', JSON.stringify(memberships));
        this.setVersion("1.0.1");
        break;
    }
  }
  setVersion = (version) => {
    window.localStorage.setItem('openy_membership_version', version);
    this.init();
  }
}

export default new Version;