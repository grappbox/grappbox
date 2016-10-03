using Newtonsoft.Json;

namespace Grappbox.Model
{
    public class Creator
    {
        [JsonProperty("id")]
        public int Id {get;set;}
        [JsonProperty("fullname")]
        public string Fullname { get; set; }
    }
}