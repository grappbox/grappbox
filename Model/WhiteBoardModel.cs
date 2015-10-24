using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class WhiteBoardModel
    {
        private List<ShapeControler> _shapeList;
        public List<ShapeControler> ShapeList
        {
            get { return _shapeList; }
            set
            {
                _shapeList = value;
            }
        }
    }
}
