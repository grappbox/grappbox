using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class UserTasksAdvancementModel
    {
        [JsonProperty("user")]
        public UsersModel User { get; set; }

        [JsonProperty("tasksToDo")]
        public int TasksToDo { get; set; }

        [JsonProperty("tasksDoing")]
        public int TasksDoing { get; set; }

        [JsonProperty("tasksDone")]
        public int TasksDone { get; set; }

        [JsonProperty("tasksLate")]
        public int TasksLate { get; set; }

        public string Fullname
        {
            get { return string.Format("{0} {1}", User.Firstname, User.Lastname); }
        }
    }
}
