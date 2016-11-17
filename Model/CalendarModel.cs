using Newtonsoft.Json;
using Newtonsoft.Json.Serialization;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    public class CalendarModel
    {
        [JsonProperty("events")]
        public ObservableCollection<Event> Events { get; set; }
    }
}