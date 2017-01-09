using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class UsersRepartitionModel
    {
        [JsonProperty("user")]
        public UsersModel User { get; set; }

        [JsonProperty("value")]
        public int Value { get; set; }

        [JsonProperty("percentage")]
        public float Percentage { get; set; }

        public string Fullname
        {
            get { return string.Format("{0} {1} ({2})", User.Firstname, User.Lastname, Value); }
        }
    }
}
