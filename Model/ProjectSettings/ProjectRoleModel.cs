using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectRoleModel
    {
        [JsonProperty("roleId")]
        public int RoleId { get; set; }
        [JsonProperty("projectId")]
        public int ProjectId { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("teamTimeline")]
        public int TeamTimeline { get; set; }
        [JsonProperty("customerTimeline")]
        public int CustomerTimeline { get; set; }
        [JsonProperty("gantt")]
        public int Gantt { get; set; }
        [JsonProperty("whiteboard")]
        public int Whiteboard { get; set; }
        [JsonProperty("bugtracker")]
        public int Bugtracker { get; set; }
        [JsonProperty("event")]
        public int Event { get; set; }
        [JsonProperty("task")]
        public int Task { get; set; }
        [JsonProperty("projectSettings")]
        public int ProjectSettings { get; set; }
        [JsonProperty("cloud")]
        public int Cloud { get; set; }
    }
}
