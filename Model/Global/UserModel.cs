using Newtonsoft.Json;

namespace GrappBox.Model
{
    public class UserModel
    {
        [JsonProperty("id")]
        public int Id {get;set;}
        [JsonProperty("firstname")]
        public string Firstname { get; set; }
        [JsonProperty("lastname")]
        public string Lastname { get; set; }
        [JsonProperty("percent")]
        public int Percent { get; set; }
    }
}