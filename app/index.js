function groupBy(objectArray, property) {
  return objectArray.reduce(function (acc, obj) {
    var key = obj[property];
    if (!acc[key]) {
      acc[key] = [];
    }
    acc[key].push(obj);
    return acc;
  }, {});
}

var app = new Vue({
  el: '#app',
  data: {
    showingaddModal: false,
    showingeditModal: false,
    showingdeleteModal: false,
    showinguploadimgModal: false,
    showingtaggingModal: false,
    errorMessage: "",
    successMessage: "OK",
    users: [],
    newUser: { title: "", email: "", mobile: "" },
    clickedUser: {},
    tags: [],
    tagsGrouped: []
    // userToEdit: {
    //   user: null,
    //   fields: 
    //     [
    //       {
    //         value: '',
    //         edit: false,
    //         name: 'title',
    //         id: 'eip_title'
    //       },
    //       {
    //         value: '',
    //         edit: false,
    //         name: 'text',
    //         id: 'eip_text'
    //       },
    //     ]
    // }
  },

  mounted: function () {
    console.log("Vue.js is running 2...");
    this.getAllUsers();
    this.getAllTags();
  },

  methods: {
    getAllUsers: function () {
      axios.get('/camillo/api/?api=article&action=list')
        .then(function (response) {
          console.log(response);

          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.users = response.data;

            if (app.clickedUser)
            app.clickedUser = app.users.find(function(elem){
              return elem.id_article == app.clickedUser.id_article;
            });
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    getAllTags: function () {
      axios.get('/camillo/api/?api=article&action=tag_list')
        .then(function (response) {
          console.log(response);

          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.tags = response.data;
            app.tagsGrouped = groupBy(app.tags, 'tag_type');
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    addUser: function () {
      var formData = app.toFormData(app.newUser);
      axios.post('/camillo/api/?api=article&action=new', formData)
        .then(function (response) {
          console.log(response);
          app.newUser = { username: "", email: "", mobile: "" };

          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.successMessage = response.data.msg;
            app.getAllUsers();
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    updateUser: function () {
      // var formData = app.toFormData({
      //   id_article: app.userToEdit.user.id_article,
      //   title: app.userToEdit.fields[0].value,
      //   content: app.userToEdit.fields[0].value,
      // });
      var formData = app.toFormData(app.clickedUser);
      axios.post('/camillo/api/?api=article&action=update', formData)
        .then(function (response) {
          console.log(response);
          app.clickedUser = { username: "", email: "", mobile: "" };

          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.successMessage = response.data.msg;
            app.getAllUsers();
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    toggleActive: function (id_article) {
      //var id = app.clickedUser.id;
      axios.get('/camillo/api/?api=article&action=toggleactive&id_article='+id_article)
        .then(function (response) {
          console.log(response);
          
          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.successMessage = response.data.msg;
            app.getAllUsers();
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    // editUser(user){
    //   app.userToEdit.user = user;
    //   app.userToEdit.fields[0].value = user.title;
    //   app.userToEdit.fields[1].value = user.text;
    //   app.showingeditModal = true;
    // },

    selectUser(user) {
      app.clickedUser = user;
    },

    toFormData: function (obj) {
      var form_data = new FormData();
      for (var key in obj) {
        form_data.append(key, obj[key]);
      }
      return form_data;
    },

    clearMessage: function (argument) {
      app.errorMessage = "";
      app.successMessage = "";
    },

    // toggleEdit: function(field, e) {
    //   field.edit = true;
    //   this.$nextTick(()=>{
    //     this.$refs[field.id][0].focus();
    //   })
    // },
    
    // saveEdit: function(field) {
    //   field.edit = false;
    // }

    uploadImage: function () {

      this.file = this.$refs.file.files[0];

      let formData = new FormData();
      formData.append('upfile', this.file);

      axios.post('/camillo/api/?api=article&action=uploadimg&id_article='+app.clickedUser.id_article, formData,
      {
         headers: {
           'Content-Type': 'multipart/form-data'
         }
      })
      .then(function (response) {
        console.log(response);

        if (response.data.status=='ERR') {
          app.errorMessage = response.data.msg;
        } else {
          app.successMessage = response.data.msg;
          app.getAllUsers();
        }

      })
      .catch(function (error) {
        app.errorMessage = "uploadImage: "+error;
      });

    },


    toggleTag: function (id_tag) {
      //var id = app.clickedUser.id;
      axios.get('/camillo/api/?api=article&action=toggletag&id_tag='+id_tag+'&id_article='+app.clickedUser.id_article)
        .then(function (response) {
          console.log(response);
          
          if (response.data.status=='ERR') {
            app.errorMessage = response.data.msg;
          } else {
            app.successMessage = response.data.msg;
            app.getAllUsers();

            //app.clickedUser = app.users.find(function(elem){
            //  return elem.id_article == app.clickedUser.id_article;
            //});
            //app.showingtaggingModal = false;
            //app.showingtaggingModal = true;
          }
        })
        .catch(function (error) {
          app.errorMessage = error;
        });
    },

    hasTag: function (id_tag) {
      //console.log('id_tag:'+id_tag);
      tagsFound = app.clickedUser.tags?.filter(function(elem){
        return(elem.id_tag == id_tag);
      });
      return tagsFound?.length > 0;
    }

  }
});