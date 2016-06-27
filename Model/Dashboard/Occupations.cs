using GrappBox.ApiCom;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    public class Occupations
    {
        public class OccupationUser
        {
            private string _id;
            [JsonProperty("id")]
            public string Id
            {
                get { return _id; }
                set { _id = value; }
            }
            private string _firstName;
            [JsonProperty("firstName")]
            public string FirstName
            {
                get { return _firstName; }
                set { _firstName = value; }
            }
            private string _lastName;
            [JsonProperty("lastName")]
            public string LastName
            {
                get { return _lastName; }
                set { _lastName = value; }
            }
        }
        public string UserName
        {
            get { return User.FirstName + " " + User.LastName; }
        }
        private string _name;
        [JsonProperty("name")]
        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private OccupationUser _user;
        [JsonProperty("user")]
        public OccupationUser User
        {
            get { return _user; }
            set { _user = value; }
        }
        private string _occupation;
        [JsonProperty("occupation")]
        public string Occupation
        {
            get { return _occupation; }
            set { _occupation = value; }
        }
        private int _tasks_begun;
        [JsonProperty("number_of_tasks_begun")]
        public int Tasks_begun
        {
            get { return _tasks_begun; }
            set { _tasks_begun = value; }
        }
        private int _tasks_ongoing;
        [JsonProperty("number_of_ongoing_tasks")]
        public int Tasks_Ongoing
        {
            get { return _tasks_ongoing; }
            set { _tasks_ongoing = value; }
        }
    }
}
