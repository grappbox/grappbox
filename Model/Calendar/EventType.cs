using Newtonsoft.Json;

namespace GrappBox.Model
{
    public class EventType
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
    }
}