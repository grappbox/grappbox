using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class BugtrackerModel
    {
        public BugtrackerModel()
        {
            State = new IdNameModel();
            Tags = new List<IdNameModel>();
            Users = new List<Users>();
        }

        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("creator")]
        public Creator Creator { get; set; }

        [JsonProperty("projectId")]
        public int ProjectId { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("parentId")]
        public object parent { get { return ParentId; } set { if (value == null) ParentId = 0; else ParentId = Convert.ToInt32(value); } }
        public int ParentId { get; set; }

        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }

        [JsonProperty("editedAt")]
        public DateModel EditedAt { get; set; }

        [JsonProperty("deletedAt")]
        public DateModel DeletedAt { get; set; }

        [JsonProperty("state")]
        public IdNameModel State { get; set; }

        [JsonProperty("tags")]
        public List<IdNameModel> Tags { get; set; }

        [JsonProperty("users")]
        public List<Users> Users { get; set; }

        public string Infos
        {
            get
            {
                return "Created By " + Creator.Fullname + " at " + DateTime.Parse(CreatedAt.date);
            }
        }
    }
}
