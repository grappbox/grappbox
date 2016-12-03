using Grappbox.ViewModel;
using Newtonsoft.Json;
using Newtonsoft.Json.Serialization;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class CalendarModel
    {
        [JsonProperty("events")]
        public ObservableCollection<EventViewModel> Events { get; set; }
    }
}