using GrappBox.Model;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.ApiCom
{
    class User
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("firstname")]
        public string Firstname{ get; set; }
        [JsonProperty("lastname")]
        public string Lastname{ get; set; }
        [JsonProperty("email")]
        public string Email{ get; set; }
        [JsonProperty("token")]
        public string Token{ get; set; }
        [JsonProperty("avatar")]
        public DateModel AvatarDate{ get; set; }

        public BitmapImage Avatar { get; set; }

        static private User instance = null;
        static public User GetUser()
        {
            return instance;
        }
        public User()
        {
            Avatar = null;
            instance = this;
        }
    }
}