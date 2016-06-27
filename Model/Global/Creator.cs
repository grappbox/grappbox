using Newtonsoft.Json;

namespace GrappBox.Model
{
    public class Creator
    {
        [JsonProperty("id")]
        public int Id {get;set;}
        [JsonProperty("fullname")]
        public string Fullname { get; set; }
    }
}