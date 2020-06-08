<template>
  <div>
    <h4 slot="header" class="text-left mt-3">Gallery</h4>
    <b-row>
      <b-col v-for="image in gallery" :key="image.id"  cols="4">
        <div class="gallery-item">
          <img :src="image.url" />
          <span class="gallery-delete" @click="deleteGallery(image)"><i class="fa fa-trash"></i></span>
        </div>
      </b-col>
      <b-col>
        <div class="gallery-item-add text-center d-flex justify-content-center align-items-center">
          <i class="fa fa-plus" @click="selectUpload" style="font-size: 30px"></i>
          <input
            type="file"
            accept="image/jpeg, image/png"
            class="d-none"
            ref="inputImage"
            @change="onChangeImage"
          />
        </div>
      </b-col>
    </b-row>
  </div>
</template>
<script>
export default {
  data() {
    return {
      gallery: [],
      user: window.Store.user
    };
  },
  mounted() {
    const params = new window.Form({ user_id: this.user.id })
    window.App.get(
      `/api/v1/gallery`,
      params
    ).then(res => {
      this.gallery = res.data.data;
    });
  },
  methods: {
    selectUpload() {
      this.$refs.inputImage.click();
    },
    onChangeImage(e) {
      window.Utils.getFileImageData(e.target).then(response => {
        this.avatarImage = response;
        const uploadPhoto = e.target.files[0];
        uploadPhoto.id = this.generateUUID();
        const formData = new window.Form({
          photos: [uploadPhoto]
        });
        window.App.postData(`/api/v1/gallery`, formData).then(res => {
          this.notify("success", "Gallery is updated.");
          this.gallery = this.gallery.concat(res.data.data)
        });
      });
    },
    deleteGallery(gallery) {
      const index = this.gallery.findIndex(item=> item.id = gallery.id)
      this.gallery.splice(index, 1)
      App.delete(`/api/v1/gallery/${gallery.id}` , new window.Form(gallery)).then(res=>{
        this.notify("success", "Gallery is deleted.");
      })
    },  
    generateUUID: function() {
      // Public Domain/MIT
      var d = new Date().getTime();
      if (
        typeof performance !== "undefined" &&
        typeof performance.now === "function"
      ) {
        d += performance.now(); //use high-precision timer if available
      }
      var newGuid = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        function(c) {
          var r = (d + Math.random() * 16) % 16 | 0;
          d = Math.floor(d / 16);
          return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
        }
      );

      return newGuid;
    }
  }
};
</script>
<style lang="scss" scoped>
.gallery-item {
  width: 200px;
  height: 200px;
  border-radius: 10px;
  overflow: hidden;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .gallery-delete {
    display: none;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-100%, -50%);
    font-size: 40px;
  }
  &:hover {
    img {
      opacity: 0.5;
    }
    .gallery-delete {
      display: block;
    }
  }
}
.gallery-item-add {
  width: 200px;
  height: 200px;
  border: dashed 1px red;
  border-radius: 10px;
  
}
</style>