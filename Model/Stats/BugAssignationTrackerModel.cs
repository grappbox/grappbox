using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class BugAssignationTrackerModel
    {
        [JsonProperty("assigned")]
        public int Assigned { get; set; }

        [JsonProperty("unassigned")]
        public int Unassigned { get; set; }
    }
}
