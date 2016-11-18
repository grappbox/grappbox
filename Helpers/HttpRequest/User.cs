using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace Grappbox.HttpRequest
{
    public class User
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
        public string AvatarDate{ get; set; }
        [JsonProperty("is_client")]
        public bool IsClient { get; set; }
        public BitmapImage Avatar { get; set; }
        public string FullName
        {
            get { return Firstname + " " + Lastname; }
        }

        public User()
        {
            Avatar = null;
        }
    }
}