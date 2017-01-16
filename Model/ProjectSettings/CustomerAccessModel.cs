using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class CustomerAccessModel
    {
        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("token")]
        public string Token { get; set; }

        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("project_id")]
        public string ProjectId { get; set; }

        public string CustomerUri
        {
            get
            {
                return string.Format("https://grappbox.com/register/customer?t=<{0}>", Token);
            }
        }
    }
}
