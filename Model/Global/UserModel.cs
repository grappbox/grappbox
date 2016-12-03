using Grappbox.ViewModel;
using Newtonsoft.Json;
using System.Collections.ObjectModel;

namespace Grappbox.Model
{
    public class UserModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("firstname")]
        public string Firstname { get; set; }
        [JsonProperty("lastname")]
        public string Lastname { get; set; }
        [JsonProperty("email")]
        public string Email { get; set; }
        [JsonProperty("token")]
        public string Token { get; set; }
        [JsonProperty("avatar")]
        public string AvatarDate { get; set; }
        [JsonProperty("is_client")]
        public bool IsClient { get; set; }
        public int Percent { get; set; }
        public string FullName
        {
            get
            {
                return Firstname + " " + Lastname;
            }
        }
    }
}