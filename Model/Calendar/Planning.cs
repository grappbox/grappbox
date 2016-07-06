using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class Planning
    {
        [JsonProperty("events")]
        public ObservableCollection<Event> Events { get; set; }
        [JsonProperty("tasks")]
        public ObservableCollection<Task> Tasks { get; set; }
    }
}
