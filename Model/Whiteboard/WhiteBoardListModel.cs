using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class WhiteBoardListModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("userId")]
        public int UserId { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("updatorId")]
        public int UpdatorId { get; set; }
        [JsonProperty("updatedAt")]
        public DateModel UpdatedAt { get; set; }
        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }
        [JsonProperty("deledtedAt")]
        public DateModel DeledtedAt { get; set; }
    }
}
