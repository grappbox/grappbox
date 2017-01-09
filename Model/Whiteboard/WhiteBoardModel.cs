using Grappbox.Model;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace Grappbox.Model
{

    public class WhiteBoardModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
        [JsonProperty("userId")]
        public int UserId { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("updatorId")]
        public int UpdatorId { get; set; }
        [JsonProperty("updatedAt")]
        public DateTime? UpdatedAt { get; set; }
        [JsonProperty("createdAt")]
        public DateTime? CreatedAt { get; set; }
        [JsonProperty("deletedAt")]
        public DateTime? DeletedAt { get; set; }
        [JsonProperty("content")]
        public ObservableCollection<WhiteboardObject> Object { get; set; }
    }
}
