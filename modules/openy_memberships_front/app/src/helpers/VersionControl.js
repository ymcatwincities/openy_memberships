class Version {
  init = () => {
    let version = window.localStorage.getItem('openy_membership_version');
    if (!version) {
      version = 0;
    }
    switch (version) {
      case 0:
        window.localStorage.removeItem('memberships');
        this.setVersion(1);
        break;
    }
  }
  setVersion = (version) => {
    window.localStorage.setItem('openy_membership_version', version);
  }
}

export default new Version;