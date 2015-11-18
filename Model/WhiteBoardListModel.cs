using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class WhiteBoardListModel
    {
        private List<String> _projetcs;
        public List<String> Projects
        {
            get { return _projetcs; }
            set { _projetcs = value; }
        }
        private List<String> _whiteboards;
        public List<String> WhiteBoards
        {
            get { return _whiteboards; }
            set { _whiteboards = value; }
        }
        public WhiteBoardListModel()
        {
            Projects.Add("Projects_Manhattan");
            Projects.Add("Projects_21");
            Projects.Add("Projects_Moebius");
            WhiteBoards.Add("WB_1");
        }
    }
}
