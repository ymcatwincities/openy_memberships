class User {
  token = null;
  getToken = (force) => {
    if(this.token && !force) {
      return new Promise(resolve => {
        resolve(this.token)
      })
    }
    return window.jQuery
      .ajax({
        url: "/session/token"
      })
      .then(token => {
        this.token = token
        return token
      });
  }
  getUserInfo = () => { 
    return this.getToken().then((token) => {
      return window.jQuery.ajax({
        url: "/jsonapi",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
        }
      });
    });
  }
}
export default new User