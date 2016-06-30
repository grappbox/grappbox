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
        public string Avatar{ get; set; }

        public BitmapImage Img
        {
            get
            {
                string base64 = Avatar;
                if (base64 == null || base64 == "")
                {
                    BitmapImage bmi = new BitmapImage();
                    Uri uri = new Uri("ms-appx:///Assets/user.png");
                    bmi.UriSource = uri;
                    return bmi;
                }
                else
                {
                    var imageBytes = Convert.FromBase64String(base64);
                    using (InMemoryRandomAccessStream ms = new InMemoryRandomAccessStream())
                    {
                        using (DataWriter writer = new DataWriter(ms.GetOutputStreamAt(0)))
                        {
                            writer.WriteBytes((byte[])imageBytes);
                            writer.StoreAsync().GetResults();
                        }

                        var image = new BitmapImage();
                        image.SetSource(ms);
                        return image;
                    }
                }
            }
        }

        static private User instance = null;
        static public User GetUser()
        {
            return instance;
        }
        public User()
        {
            instance = this;
        }
    }
}