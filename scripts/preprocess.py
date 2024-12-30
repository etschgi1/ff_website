# preprocess files to be used on new website!
import os
cur_dir = os.path.dirname(__file__)
SOURCE_FOLDER = cur_dir + "/exportraw_aktuelles"
DEST_FOLDER = cur_dir + "/preproc_aktuelles"
if not os.path.exists(DEST_FOLDER):
    os.makedirs(DEST_FOLDER)

files = [f for f in os.listdir(SOURCE_FOLDER) if os.path.isfile(os.path.join(SOURCE_FOLDER, f))]

for file in files: 
    content = None
    with open(os.path.join(SOURCE_FOLDER, file) , "r") as f:
        content = f.readlines()
    articelbody_start = next((i for i, line in enumerate(content) if '<div itemprop="articleBody"' in line), None)
    articelbody_end = next((i for i, line in enumerate(content[articelbody_start:], articelbody_start) if '</div>' in line), None)
    if articelbody_start is not None and articelbody_end is not None:
        article_body = content[articelbody_start + 1:articelbody_end]
        with open(os.path.join(DEST_FOLDER, file), "w") as f:
            f.writelines(article_body)
    else: 
        assert False
    # TODO checken ob es eine image gallery ist
    # TODO img tags ändern, damit es passt. 
    