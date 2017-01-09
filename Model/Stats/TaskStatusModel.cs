using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class TaskStatusModel
    {
        [JsonProperty("done")]
        public int Done { get; set; }

        [JsonProperty("doing")]
        public int Doing { get; set; }

        [JsonProperty("toDo")]
        public int ToDo { get; set; }

        [JsonProperty("late")]
        public int Late { get; set; }
    }
}
