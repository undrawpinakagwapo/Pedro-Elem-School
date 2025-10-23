<div class="table-responsive">
        <table class="table table-hover table-bordered" id="mysubjectlist">
        <thead>
            <tr>
                <th>No.#</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
            </tr>
        </thead>
        <tbody>
        <?php if($list): ?>
            <?php $i=1;?>
            <?php foreach ($list as $value): ?>
                <tr>
                    <td><?=$i?></td>
                    <td><?= $value['code'] ?></td>
                    <td><?= $value['name'] ?></td>
                </tr>
                <?php $i++;?>
            <?php endforeach; ?>
        <?php endif; ?>
        
        </tbody>
    </table>
    
</div>