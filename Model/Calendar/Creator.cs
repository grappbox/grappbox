using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class Creator
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("firstname")]
        public string FirstName { get; set; }

        [JsonProperty("lastname")]
        public string LastName { get; set; }

        public string FullName
        {
            get
            {
                return FirstName + " " + LastName;
            }
        }

        public string FirstNameLabel
        {
            get
            {
                return "FirstName: " + FirstName;
            }
        }

        public string LastNameLabel
        {
            get
            {
                return "LastName: " + LastName;
            }
        }

        public string FullNameLabel
        {
            get
            {
                return "Name: " + FullName;
            }
        }
    }
}