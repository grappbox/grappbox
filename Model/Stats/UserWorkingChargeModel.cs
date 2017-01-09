using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class UserWorkingChargeModel
    {
        [JsonProperty("user")]
        public UsersModel User { get; set; }

        [JsonProperty("charge")]
        public int Charge { get; set; }

        public string Fullname
        {
            get { return string.Format("{0} {1} ({2}%)", User.Firstname, User.Lastname, Charge); }
        }
    }
}
